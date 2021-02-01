<?php


namespace OneDark\TombstoneRedis\Constants;


class Expire
{
    const ONE_DAY = 86400;
    const TWO_DAYS = self::ONE_DAY * 2;
    const ONE_WEEK = self::ONE_DAY * 7;
    const FOUR_WEEKS = self::ONE_WEEK * 4;
}
