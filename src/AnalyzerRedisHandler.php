<?php

declare(strict_types=1);

namespace OneDark\TombstoneRedis;

use OneDark\TombstoneRedis\Constants\Expire;
use Redis;
use Scheb\Tombstone\Core\Model\Vampire;
use Scheb\Tombstone\Logger\Formatter\AnalyzerLogFormatter;
use Scheb\Tombstone\Logger\Handler\AbstractHandler;
use Scheb\Tombstone\Logger\Formatter\FormatterInterface;

class AnalyzerRedisHandler extends AbstractHandler
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
    private $vampires = [];
    /**
     * @var string
     */
    private $cummulativeKey = '';

    public function __construct(Redis $client, $path = 'tombstones-logs', int $expire = Expire::FOUR_WEEKS)
    {
        $this->client = $client;
        $this->root = $path;
        $this->expire = $expire;
    }

    public function flush(): void
    {
        $key = $this->root . ':' . hash('sha512', $this->cummulativeKey);
        $this->client->set($key, json_encode($this->vampires));
        $this->client->expire($key, $this->expire);
    }

    public function log(Vampire $vampire): void
    {
        $date = date('Ymd');
        $hash = $vampire->getTombstone()->getHash();
        $key = $this->root . ':' . sprintf('%s-%s.tombstone', $hash, $date);

        $this->cummulativeKey .= $key;
        $this->vampires[$key] = $this->getFormatter()->format($vampire);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new AnalyzerLogFormatter();
    }
}