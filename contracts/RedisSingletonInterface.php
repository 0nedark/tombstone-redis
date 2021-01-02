<?php


namespace OneDark\TombstoneRedis\Contracts;


use Redis;

interface RedisSingletonInterface
{
    static function instance(): Redis;
}
