<?php
class Timer
{
    private static $start = .0;

    static function start()
    {
        self::$start = microtime(true);
    }

    static function finish()
    {
        return microtime(true) - self::$start;
    }
}
?>