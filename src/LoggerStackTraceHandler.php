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
    /**
     * @var array
     */
    private $traces = [];
    /**
     * @var string
     */
    private $cummulativeKey = '';

    public function __construct(Redis $client, $path = 'tombstones-traces', $expire = Expire::TWO_DAYS)
    {
        $this->client = $client;
        $this->root = $path;
        $this->expire = $expire;
    }

    public function flush(): void
    {
        $key = $this->root . ':' . hash('sha512', $this->cummulativeKey);
        $this->client->set($key, json_encode($this->traces));
        $this->client->expire($key, $this->expire);
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

            $key = $this->root . ':' . implode(':', $path);
            $this->cummulativeKey .= $key;
            $this->traces[$key] = $trace;
        }
    }
}
