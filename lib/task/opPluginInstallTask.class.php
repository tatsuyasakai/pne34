<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

class opPluginInstallTask extends sfPluginInstallTask
{
  protected $pluginManager = null;

  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'The plugin name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('stability', 's', sfCommandOption::PARAMETER_REQUIRED, 'The preferred stability (stable, beta, alpha)', null),
      new sfCommandOption('release', 'r', sfCommandOption::PARAMETER_REQUIRED, 'The preferred version', null),
      new sfCommandOption('channel', 'c', sfCommandOption::PARAMETER_REQUIRED, 'The PEAR channel name', 'plugins.openpne.jp'),
      new sfCommandOption('install_deps', 'd', sfCommandOption::PARAMETER_NONE, 'Whether to force installation of required dependencies', null),
      new sfCommandOption('force-license', null, sfCommandOption::PARAMETER_NONE, 'Whether to force installation even if the license is not MIT like'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
    ));

    $this->namespace        = 'opPlugin';
    $this->name             = 'install';
    $this->briefDescription = 'Installs the OpenPNE plugin';
    $this->detailedDescription = <<<EOF
The [plugin:install|INFO] task installs the OpenPNE plugin:
Call it with:

  [./symfony opPlugin:install opSamplePlugin|INFO]
EOF;
  }

  // copied from sfDoctrineBaseTask
  protected function createConfiguration($application, $env)
  {
    $configuration = parent::createConfiguration($application, $env);

    $autoloader = sfSimpleAutoload::getInstance();
    $config = new sfAutoloadConfigHandler();
    $mapping = $config->evaluate($configuration->getConfigPaths('config/autoload.yml'));
    foreach ($mapping as $class => $file)
    {
      $autoloader->setClassPath($class, $file);
    }
    $autoloader->register();

    return $configuration;
  }

  protected function isSnsConfigTableExists()
  {
    try
    {
      if (class_exists('SnsConfigTable'))
      {
        return Doctrine_Manager::connection()
          ->import
          ->tableExists(Doctrine::getTable('SnsConfig')->getTableName());
      }
    }
    catch (Doctrine_Connection_Exception $e) { }

    return false;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // Remove E_STRICT and E_DEPRECATED from error_reporting
    error_reporting(error_reporting() & ~(E_STRICT | E_DEPRECATED));

    if (sfConfig::get('op_http_proxy'))
    {
      $config = $this->getPluginManager()->getEnvironment()->getConfig();
      $config->set('http_proxy', sfConfig::get('op_http_proxy'), 'user', 'pear.php.net');
    }

    if ($this->isSelfInstalledPlugins($arguments['name']))
    {
      $str = "\"%s\" is already installed manually, so it will not be reinstalled.\n"
           . "If you want to manage it automatically, delete it manually and retry this command.";
      $this->logBlock(sprintf($str, $arguments['name']), 'INFO');
      return false;
    }

    try
    {
      $isExists = $this->isPluginExists($arguments['name']);
      parent::execute($arguments, $options);

      if (count(sfFinder::type('file')->name('databases.yml')->in(sfConfig::get('sf_config_dir'))) && !$isExists)
      {
        $databaseManager = new sfDatabaseManager($this->configuration);
        if ($this->isSnsConfigTableExists())
        {
          Doctrine::getTable('SnsConfig')->set($arguments['name'].'_needs_data_load', '1');
        }
      }
    }
    catch (sfPluginException $e)
    {
      $this->logBlock($e->getMessage(), 'ERROR');
      return false;
    }
  }

  public function getPluginManager()
  {
    if (is_null($this->pluginManager))
    {
      $this->pluginManager = new opPluginManager($this->dispatcher);
    }

    return $this->pluginManager;
  }

  public function isSelfInstalledPlugins($pluginName)
  {
    if (!$this->isPluginExists($pluginName))
    {
      return false;
    }

    $registry = $this->getPluginManager()->getEnvironment()->getRegistry();
    return !(bool)$registry->getPackage($pluginName, opPluginManager::OPENPNE_PLUGIN_CHANNEL);
  }

  public function isPluginExists($pluginName)
  {
    return in_array($pluginName, array_keys($this->configuration->getAllPluginPaths()));
  }
}
