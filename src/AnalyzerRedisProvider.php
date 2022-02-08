<?php

declare(strict_types=1);

namespace OneDark\TombstoneRedis;

use OneDark\TombstoneRedis\Contracts\RedisSingletonInterface;
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

    public function __construct(array $config, RootPath $rootDir, ConsoleOutputInterface $output)
    {
        if (!isset($config['logs']['custom']['class'])) {
            throw new \Exception('RedisAnalyzer requires config["logs"]["custom"]["class"] to be set');
        }

        $className = $config['logs']['custom']['class'];
        $reflectionClass = new \ReflectionClass($className);
        if (!$reflectionClass->implementsInterface(RedisSingletonInterface::class)) {
            throw new \Exception(sprintf('Class %s must implement %s', $className, RedisSingletonInterface::class));
        }

        $this->redis = $reflectionClass->getMethod('instance')->invoke(null);

        $this->rootDir = $rootDir;
        $this->output = $output;
        $this->root = $config['driver']['redis']['path'] ?: 'tombstones-logs';
    }

    public static function create(array $config, ConsoleOutputInterface $consoleOutput): LogProviderInterface
    {
        $rootDir = new RootPath($config['source_code']['root_directory']);

        return new self($config, $rootDir, $consoleOutput);
    }

    public function getVampires(): iterable
    {
        $batchKeys = $this->redis->keys($this->root);
        $this->output->writeln('Extracting tombstone data ...');
        $progress = $this->output->createProgressBar(count($batchKeys));

        $batches = [];
        foreach ($batchKeys as $batchKey) {
            $batch = $this->redis->get($batchKey);
            $batches[] = json_decode($batch);
            $progress->advance();
        }

        $this->output->writeln('Analyzing tombstone data ...');
        $progress = $this->output->createProgressBar(count($batches));

        foreach ($batches as $batch) {
            foreach ($batch as $line) {
                yield AnalyzerLogFormat::logToVampire($line, $this->rootDir);
            }
            $progress->advance();
        }

        $this->output->writeln();
    }
}
