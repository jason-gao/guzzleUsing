<?php
/**
 * Desc: 计算程序运行seconds数
 * Created by PhpStorm.
 * User: jasong
 * Date: 2017/12/14 17:19
 */

namespace guzzleUsing\Library;

class CalcTime
{

    private static $startTime;
    private static $endTime;
    private static $useTime;

    public static function start()
    {
        self::$startTime = self::timeToFloat();
    }

    public static function end()
    {
        self::$endTime = self::timeToFloat();
    }

    public static function timeToFloat()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public static function useTime()
    {
        self::$useTime = self::$endTime - self::$startTime;

        return self::$useTime;
    }


    public static function echoUseTime($index = '')
    {
        echo '消耗时间: ' . $index . ' ' . self::useTime() . "s\n";
    }

}