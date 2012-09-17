<?php

class Zipmark_Disbursements extends Zipmark_Pager {
  /**
   * Find all disbursements
   *
   * @param  string                   $params Parameters for find
   * @param  Zipmark_Client           $client Client object to connect to service
   *
   * @return Zipmark_Disbursements            A list of Disbursements
   */
  public static function get($params = null, $client = null) {
    $list = new Zipmark_Disbursements(null, $client);
    $list->setHref($list->pathFor());
    $list->_loadFrom($list->pathFor(), $params);
    return $list;
  }
}

?>
