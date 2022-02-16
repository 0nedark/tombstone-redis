<?php


namespace OneDark\TombstoneRedis\Constants;


class Expire
{
    const ONE_HOUR = 3600;
    const TWO_HOURS = self::ONE_HOUR * 2;
    const ONE_DAY = self::ONE_HOUR * 24;
    const TWO_DAYS = self::ONE_DAY * 2;
    const ONE_WEEK = self::ONE_DAY * 7;
    const FOUR_WEEKS = self::ONE_WEEK * 4;
}
