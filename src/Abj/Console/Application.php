<?php
/**
 * Created by Adam Jakab.
 * Date: 24/11/16
 * Time: 14.21
 */

namespace Abj\Console;

use Abj\Configuration\EntityManagerProvider;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Application
 *
 * @package Abj\Console
 */
class Application extends SymfonyConsoleApplication
{
  /**
   * Application constructor.
   *
   * @param string $name
   * @param string $version
   *
   */
  public function __construct($name, $version)
  {
    parent::__construct($name, $version);

    //Add Doctrine support to console
    EntityManagerProvider::setupApplication($this);

    //Add commands from this application
    $this->addApplicationCommands();
  }

  /**
   * Runs the current application.
   *
   * @param InputInterface  $input An Input instance
   * @param OutputInterface $output An Output instance
   *
   * @return int 0 if everything went fine, or an error code
   */
  public function run(InputInterface $input = NULL, OutputInterface $output = NULL)
  {
    try
    {
      $res = parent::run($input, $output);
    } catch(\Exception $e)
    {
      $res = $e->getCode();
    }

    return $res;
  }

  /**
   * enumerate and add commands
   */
  protected function addApplicationCommands()
  {
    $commands = $this->enumerateCommands();
    foreach ($commands as $command)
    {
      $this->add(new $command);
    }
  }

  /**
   * Returns array of FQCN of command classes
   * @return array
   */
  protected function enumerateCommands()
  {
    $answer = [];
    $searchPath = realpath(PROJECT_PATH . DIRECTORY_SEPARATOR . 'src');
    $iterator = new \RecursiveDirectoryIterator($searchPath);
    foreach (new \RecursiveIteratorIterator($iterator) as $file)
    {
      if (strpos($file, 'Command.php') !== FALSE)
      {
        if (is_file($file))
        {
          $cmdClassPath = str_replace($searchPath . DIRECTORY_SEPARATOR, '', $file);
          $cmdClassPath = str_replace('.php', '', $cmdClassPath);
          $cmdClassPath = str_replace('/', '\\', $cmdClassPath);
          $classImplements = class_implements($cmdClassPath);
          if (is_array($classImplements) && in_array('Abj\Console\CommandInterface', $classImplements))
          {
            $answer[] = $cmdClassPath;
          }
        }
      }
    }
    return $answer;
  }
}

