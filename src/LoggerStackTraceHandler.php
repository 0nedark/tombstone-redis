<?php


namespace OneDark\TombstoneRedis;


use DateTime;
use OneDark\TombstoneRedis\Constants\Expire;
use Redis;
use Scheb\Tombstone\Core\Model\Vampire;
use Scheb\Tombstone\Logger\Handler\AbstractHandler;

class LoggerStackTraceHandler extends AbstractHandler
{
    /**
     * @var Redis
     */
    private $client;
    /**
     * @var string
     */
    private $root;
    /**
     * @var int
     */
    private $expire;

    public function __construct(Redis $client, $path = 'tombstones', $expire = Expire::TWO_DAYS)
    {
        $this->client = $client;
        $this->root = $path;
        $this->expire = $expire;
    }

    public function log(Vampire $vampire): void
    {
        $traces = null;

        try {
            throw new \Exception('Tombstone');
        } catch (\Exception $exception) {
            $traces = $exception->getTrace();

            do {
                $head = array_shift($traces);
            } while (strpos($head['file'], 'tombstone-function') === false);
        }

        $path = [];
        foreach ($traces as $trace) {
            $path[] = $trace['file'] . '|' . $trace['line'];
            $trace['created_at'] = new DateTime();
            $key = $this->getLogKey($path);
            $value = json_encode($trace);

            $this->client->set($key, $value);
            $this->client->expire($key, $this->expire);
        }
    }

    private function getLogKey(array $path): string
    {
        return $this->root . ':traces:' . implode(':', $path);
    }
}
