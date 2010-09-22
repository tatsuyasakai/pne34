<?php
/** iii
 * ashiato components.
 *
 * @package    OpenPNE
 * @subpackage ashiato
 * @author     uechoco
 */
class ashiatoComponents extends sfComponents
{
 
  public function executeAshiatoHomeList()
  {
    $this->id = $this->getRequestParameter('id', $this->getUser()->getMemberId());
    $this->pager = AshiatoPeer::getAshiatoMemberListPager($this->id, 1, 5);
  }
}
