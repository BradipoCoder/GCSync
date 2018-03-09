<?php
/**
 * Created by Adam Jakab.
 * Date: 02/12/16
 * Time: 17.59
 */

namespace Abj\Console;

use Abj\Configuration\ConfigurationEnumerator;
use Abj\Configuration\EntityManagerProvider;
use Abj\Configuration\UserConfiguration;
use Abj\Entity\User;
use Abj\Logger\ConsoleLogger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * All commands extending this class must supply user-config-file option and will have
 * a) $this->userConfiguration
 * b) $this->currentUser
 * in return
 *
 * Class UserAwareCommand
 * @package Abj\Console
 */
class UserAwareCommand extends Command
{
    /** @var  UserConfiguration */
    protected $userConfiguration;

    /** @var  User */
    protected $currentUser;

    /**
     * @param string $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

  /**
   * Returns a command specific sub-key from under commands.[command_name] from the user config file
   *
   * @param string $name
   * @throws \Exception
   * @return mixed
   */
    protected function getCommandSpecificConfiguration($name, $default = null)
    {
      $answer = $default;

      $commandSectionPath = "commands." . str_replace(":", "_", $this->commandName) . "." . $name;
      try {
        $answer = $this->userConfiguration->get($commandSectionPath);
      } catch(\Exception $e)
      {
        $answer = $default;
      }

      return $answer;
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        parent::configure();
        $this->addOption('user-config-file', '', InputOption::VALUE_REQUIRED, 'The user configuration file to use (do not use .yml extension).');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        if (!$input->hasOption("user-config-file") || !$input->getOption("user-config-file")) {
            throw new \Exception("Invalid User Command! User configuration file is not specified.");
        }

        //check user config file before going any further (@throws ConfigurationNotFoundException)
        $this->userConfiguration = ConfigurationEnumerator::getConfigurationForUser($input->getOption("user-config-file"));

        $useDatabase = $this->userConfiguration->get("application.use_database");
        if($useDatabase)
        {
            // set current user
            $em = EntityManagerProvider::getEntityManager();
            $username = $this->userConfiguration->get("application.username");
            $this->currentUser = $em->getRepository('Abj\Entity\User')->findOneBy(['username' => $username]);

            if (!$this->currentUser) {
                $this->currentUser = new User();
                $this->currentUser->setUsername($username);
                $em->persist($this->currentUser);
                $em->flush($this->currentUser);
            }
        }

        //execute the specific command in the extending class
        return $this->executeCommand();
    }

    /**
     * Execute Command
     * @throws \LogicException
     */
    protected function executeCommand()
    {
        throw new \LogicException('You must override the executeCommand() method in the concrete user command class.');
    }
}