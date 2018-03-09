<?php
/**
 * Created by Adam Jakab.
 * Date: 02/12/16
 * Time: 10.13
 */

namespace Abj\Configuration;

use Abj\Console\Application;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\DBAL\Tools\Console\ConsoleRunner as DBALConsoleRunner;
use Doctrine\ORM\Tools\Console\ConsoleRunner as ORMConsoleRunner;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Doctrine\ORM\Tools\Setup;

/**
 * Class EntityManagerProvider
 *
 * This class will use current hostname for database configuration file, such as:
 * config/db/[hostname].yml
 *
 * @package Abj\Configuration
 */
class EntityManagerProvider
{
  /** @var EntityManager */
  protected static $entityManager;

  /**
   * @return EntityManager
   *
   * @throws ORMException
   * @throws \Exception
   */
  public static function getEntityManager()
  {
    if(!class_exists("Doctrine\ORM\EntityManager") || !class_exists("Doctrine\ORM\Tools\Setup"))
    {
      throw new \Exception("This application does not have Doctrine support.");
    }

    if (!self::$entityManager)
    {
      $hostname = php_uname("n");

      $cfg = ConfigurationEnumerator::getConfigurationForDatabase($hostname);

      $isDevMode = TRUE;//@todo - move me out to config.yml

      $paths = [
        realpath(PROJECT_PATH . "/src/Abj/Entity")
      ];

      // the connection configuration
      $dbParams = $cfg->get("parameters");

      $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, NULL, NULL, FALSE);
      self::$entityManager = EntityManager::create($dbParams, $config);
    }

    return self::$entityManager;
  }

  /**
   * @param Application $application
   */
  public static function setupApplication(Application $application)
  {
    try
    {
      $em = EntityManagerProvider::getEntityManager();
    } catch(\Exception $e)
    {
      $em = FALSE;
      //print("EntityManagerProvider application setup failed: " . $e->getMessage() . "\n");
    }

    if (!$em)
    {
      return;
    }

    $doctrineHelperSet = new HelperSet(
      [
        'db' => new ConnectionHelper($em->getConnection()),
        'em' => new EntityManagerHelper($em),
      ]
    );
    $application->setHelperSet($doctrineHelperSet);

    //Add ORM command sets
    if (class_exists("\Doctrine\ORM\Tools\Console\ConsoleRunner"))
    {
      ORMConsoleRunner::addCommands($application);
    }

    //Add DBAL command sets
    if (class_exists("\Doctrine\DBAL\Tools\Console\ConsoleRunner"))
    {
      DBALConsoleRunner::addCommands($application);
    }
  }
}