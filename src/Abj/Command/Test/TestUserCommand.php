<?php
/**
 * Created by Adam Jakab.
 * Date: 07/10/15
 * Time: 14.26
 */

namespace Abj\Command\Test;

use Abj\Console\CommandInterface;
use Abj\Console\UserAwareCommand;
use Abj\Logger\ConsoleLogger;
use dawguk\GarminConnect;

/**
 * Class TestUserCommand
 * @package Abj\Command\Test
 */
class TestUserCommand extends UserAwareCommand implements CommandInterface
{
  protected $commandName = 'test:user';
  protected $commandDescription = 'Run a user test command.';
  protected $commandAliases = ['tu'];

  /**
   * @throws \Exception
   */
  protected function executeCommand()
  {
    ConsoleLogger::log("USER TEST COMMAND OK");
    //ConsoleLogger::log($this->userConfiguration);
    $this->test1();

    //https://github.com/cpfair/tapiriik/blob/master/tapiriik/services/TrainingPeaks/trainingpeaks.py
  }

  /**
   * @throws \Exception
   */
  protected function test1()
  {
    $credentials = [
      'username' => $this->userConfiguration->get('platform.garminconnect.username'),
      'password' => $this->userConfiguration->get('platform.garminconnect.password'),
    ];

    try {
      $GC = new GarminConnect($credentials);
      //$activities = $GC->getActivityList();
    } catch (\Exception $e) {
      ConsoleLogger::log("Oops: " . $e->getMessage());
    }

    if(isset($activities)) {
      print_r($activities);
    }



  }
}
