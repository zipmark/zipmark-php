<?php

class Zipmark_ApprovalRules extends Zipmark_Pager {
  /**
   * Find all approval rules
   *
   * @param  string                   $params Parameters for find
   * @param  Zipmark_Client           $client Client object to connect to service
   *
   * @return Zipmark_ApprovalRules            A list of Approval Rules
   */
  public static function get($params = null, $client = null) {
    $list = new Zipmark_ApprovalRules(null, $client);
    $list->setHref($list->pathFor());
    $list->_loadFrom($list->pathFor(), $params);
    return $list;
  }
}

?>
