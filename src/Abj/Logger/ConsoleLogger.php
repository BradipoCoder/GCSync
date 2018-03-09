<?php
/**
 * Created by Adam Jakab.
 * Date: 29/11/16
 * Time: 16.54
 */

namespace Abj\Logger;

use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger
{
    /** @var  OutputInterface */
    private static $logger;

    /** @var bool */
    private static $logToConsole = false;

    /**
     * @param string $msg
     */
    public static function log($msg)
    {
        if (self::$logToConsole)
        {
            self::$logger->writeln($msg);
        }
    }

    /**
     * @param int    $length
     * @param string $repeat
     */
    public static function hr($length = 80, $repeat = '-')
    {
        self::log(str_repeat($repeat, $length));
    }

    /**
     * @return boolean
     */
    public static function isLogToConsole(): bool
    {
        return self::$logToConsole;
    }

    /**
     * @param boolean $logToConsole
     */
    public static function setLogToConsole(bool $logToConsole)
    {
        self::$logToConsole = $logToConsole;
    }

    /**
     * @param OutputInterface $logger
     */
    public static function setLogger(OutputInterface $logger)
    {
        self::$logger = $logger;
    }
}