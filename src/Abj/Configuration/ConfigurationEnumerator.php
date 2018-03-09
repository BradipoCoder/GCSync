<?php
/**
 * Created by Adam Jakab.
 * Date: 29/11/16
 * Time: 14.40
 */

namespace Abj\Configuration;

/**
 * Class ConfigurationEnumerator
 *
 * @package Abj\Configuration
 */
class ConfigurationEnumerator
{
    /** @var array */
    private static $userConfigurations = [];
    
    /** @var array */
    private static $connectionConfigurations = [];
    
    /**
     * @param string $configFileName
     * @return UserConfiguration
     */
    public static function getConfigurationForUser($configFileName)
    {
        if (!array_key_exists($configFileName, self::$userConfigurations))
        {
            $userConfigFilePath = PROJECT_PATH . '/config/user/' . $configFileName . '.yml';
            self::$userConfigurations[$configFileName] = new UserConfiguration($userConfigFilePath);
        }
        return self::$userConfigurations[$configFileName];
    }
    
    /**
     * @param string $connectionName
     *
     * @return UserConfiguration
     */
    public static function getConfigurationForDatabase($connectionName)
    {
        if(!array_key_exists($connectionName, self::$connectionConfigurations))
        {
            $connectionConfigFilePath = PROJECT_PATH . '/config/db/' . $connectionName . '.yml';
            self::$connectionConfigurations[$connectionName] = new UserConfiguration($connectionConfigFilePath);
        }
        return self::$connectionConfigurations[$connectionName];
    }
}