<?php
/**
 * Created by Adam Jakab.
 * Date: 07/10/15
 * Time: 14.27
 */

namespace Abj\Console;

use Abj\Logger\ConsoleLogger;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Command
 *
 * @package Abj\Console
 */
class Command extends ConsoleCommand
{
    /** @var string */
    protected $commandName = '';

    /** @var string */
    protected $commandDescription = '';

    /** @var array */
    protected $commandAliases = [];

    /** @var  InputInterface */
    protected $cmdInput;

    /** @var  OutputInterface */
    protected $cmdOutput;

    /** @var bool */
    protected $logToConsole = true;

    /**
     * @param string $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        parent::configure();
        $this->setName($this->commandName);
        $this->setDescription($this->commandDescription);
        $this->setAliases($this->commandAliases);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cmdInput = $input;
        $this->cmdOutput = $output;
        $this->configureLogger();

        return true;
    }

    protected function configureLogger()
    {
        ConsoleLogger::setLogger($this->cmdOutput);
        ConsoleLogger::setLogToConsole($this->logToConsole);
    }
}