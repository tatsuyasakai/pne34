<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * This strategy builds models.
 *
 * @package    OpenPNE
 * @subpackage upgrade
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 */
class opUpgradeBuildModelStrategy extends opUpgradeAbstractStrategy
{
  public function run()
  {
    sfOpenPNEApplicationConfiguration::unregisterZend();

    $task = new sfDoctrineBuildModelTask(clone $this->options['dispatcher'], clone $this->options['formatter']);
    $task->run(array(), array('application' => 'pc_frontend', 'env' => sfConfig::get('sf_environment', 'prod')));

    $task = new sfCacheClearTask(clone $this->options['dispatcher'], clone $this->options['formatter']);
    $task->run(array(), array('application' => null, 'env' => sfConfig::get('sf_environment', 'prod')));

    sfOpenPNEApplicationConfiguration::registerZend();
  }
}

