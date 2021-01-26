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

    public function __construct(Redis $client, $path = 'tombstones', int $expire = Expire::FOUR_WEEKS)
    {
        $this->client = $client;
        $this->root = $path;
        $this->expire = $expire;
    }

    public function log(Vampire $vampire): void
    {
        $key = $this->getLogKey($vampire);
        $this->client->set($key, $this->getFormatter()->format($vampire));
        $this->client->expire($key, $this->expire);
    }

    private function getLogKey(Vampire $vampire): string
    {
        $date = date('Ymd');
        $hash = $vampire->getTombstone()->getHash();

        return $this->root . ':' . sprintf('%s-%s.tombstone', $hash, $date);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new AnalyzerLogFormatter();
    }
}