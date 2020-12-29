<?php

declare(strict_types=1);

namespace OneDark\TombstoneRedis;

use Redis;
use Scheb\Tombstone\Analyzer\Cli\ConsoleOutputInterface;
use Scheb\Tombstone\Analyzer\Log\LogProviderInterface;
use Scheb\Tombstone\Core\Format\AnalyzerLogFormat;
use Scheb\Tombstone\Core\Model\RootPath;
use function count;

class AnalyzerRedisProvider implements LogProviderInterface
{
    /**
     * @var RootPath
     */
    private $rootDir;

    /**
     * @var Redis
     */
    private $redis;

    /**
     * @var ConsoleOutputInterface
     */
    private $output;
    /**
     * @var string
     */
    private $root;

    public function __construct(RootPath $rootDir, ConsoleOutputInterface $output)
    {
        $redis = $redis = new Redis();
        $password = getenv('REDIS_PASSWORD') ?: null;

        if ($password !== null) {
            $redis->auth($password);
        }

        $redis->connect(
            getenv('REDIS_HOST') ?: '',
            getenv('REDIS_PORT') ?: 6379,
            getenv('REDIS_TIMEOUT') ?: 0.0,
            getenv('REDIS_RESERVED') ?: null,
            getenv('REDIS_RETRY_INTERVAL') ?: 0,
            getenv('REDIS_READ_TIMEOUT') ?: 0.0
        );

        $this->rootDir = $rootDir;
        $this->redis = $redis;

        $this->output = $output;
        $this->root = getenv('REDIS_TOMBSTONE_PATH') ?: 'tombstones';
    }

    public static function create(array $config, ConsoleOutputInterface $consoleOutput): LogProviderInterface
    {
        $rootDir = new RootPath($config['source_code']['root_directory']);

        return new self($rootDir, $consoleOutput);
    }

    public function getVampires(): iterable
    {
        $files = $this->redis->keys($this->root . ':*.tombstone');

        $this->output->writeln('Read analyzer log data ...');
        $progress = $this->output->createProgressBar(count($files));

        foreach ($files as $file) {
            foreach ($this->redis->xRead([$file => '0-0']) as $rows) {
                foreach ($rows as $timestamp => $line) {
                    yield AnalyzerLogFormat::logToVampire($line['data'], $this->rootDir);
                }
            }
            $progress->advance();
        }
        $this->output->writeln();
    }
}
