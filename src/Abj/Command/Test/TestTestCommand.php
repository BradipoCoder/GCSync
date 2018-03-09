<?php
/**
 * Created by Adam Jakab.
 * Date: 07/10/15
 * Time: 14.26
 */

namespace Abj\Command\Test;

use Abj\Console\Command;
use Abj\Console\CommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Abj\Logger\ConsoleLogger;

/**
 * Class TestTestCommand
 *
 * @package Abj\Command\Test
 */
class TestTestCommand extends Command implements CommandInterface
{
  protected $commandName = 'test:test';
  protected $commandDescription = 'Run a test command.';
  protected $commandAliases = ['tt'];

  /**
   * @throws \Exception
   */
  protected function executeCommand()
  {
    ConsoleLogger::log("TEST COMMAND OK");
  }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface   $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @return bool
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $res = parent::execute($input, $output);
    $this->executeCommand();
    return $res;
  }
}
