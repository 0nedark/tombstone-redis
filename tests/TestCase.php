<?php

declare(strict_types=1);

namespace OneDark\TombstoneRedis\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

// phpcs:disable Symfony.NamingConventions.ValidClassName.InvalidAbstractName
abstract class TestCase extends PHPUnitTestCase
{
    public const TOMBSTONE_ARGUMENTS = ['2014-01-01', 'label'];
    public const LOG_RECORD = '{"v":10000,"fn":"tombstone","a":["2014-01-01","label"],"f":"file","l":123,"m":"method","d":{"metaField":"metaValue"},"s":[{"f":"file1.php","l":11,"m":"ClassName->method"}],"id":"2015-01-01","im":"invoker"}';

    /**
     * Backwards compatibility for PHPUnit 7.5.
     */
    public function expectExceptionMessageMatches(string $regularExpression): void
    {
        if (method_exists(PHPUnitTestCase::class, 'expectExceptionMessageMatches')) {
            parent::expectExceptionMessageMatches($regularExpression);
        } else {
            parent::expectExceptionMessageRegExp($regularExpression);
        }
    }

    /**
     * Backwards compatibility for PHPUnit 7.5.
     */
    public static function assertDirectoryDoesNotExist(string $directory, string $message = ''): void
    {
        if (method_exists(PHPUnitTestCase::class, 'expectExceptionMessageMatches')) {
            parent::assertDirectoryDoesNotExist($directory, $message);
        } else {
            static::assertFalse(is_dir($directory), $message);
        }
    }
}
