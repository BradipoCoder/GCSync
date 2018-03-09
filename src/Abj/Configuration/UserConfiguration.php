<?php
/**
 * Created by PhpStorm.
 * User: jackisback
 * Date: 14/11/15
 * Time: 22.32
 */

namespace Abj\Configuration;

use Abj\Exception\ConfigurationNotFoundException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

/**
 * Class UserConfiguration
 *
 * @package Abj\Console
 */
class UserConfiguration
{
    /** @var array */
    private $config;
    
    /**
     * Configuration constructor.
     *
     * @throws ConfigurationNotFoundException
     * @param string $configurationFile
     */
    public function __construct($configurationFile)
    {
        if(!file_exists($configurationFile))
        {
            throw new ConfigurationNotFoundException("No configuration file($configurationFile) found!");
        }
        
        $yamlParser = new Parser();
        $config = $yamlParser->parse(file_get_contents($configurationFile));
    
        if (!is_array($config) || !isset($config["config"])) {
            throw new \InvalidArgumentException("Malformed configuration file!" . $configurationFile);
        }
 
        $this->config = $config["config"];
    }
    
    /**
     * very weak configuration getter
     *
     * @param string $path A dot separated path to deep array element like fb.app.it for $config["fb"]["app"]["id"]
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function get($path = '')
    {
        $parts = [];
        if ($path)
        {
            $parts = explode(".", $path);
        }
    
        $answer = $this->config;
        foreach ($parts as $part)
        {
            if (!array_key_exists($part, $answer))
            {
                throw new \Exception("Configuration - invalid requested path($path) part: $part");
            }
            $answer = $answer[$part];
        }
    
        return $answer;
    }
}