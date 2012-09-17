<?php

class Zipmark_VendorRelationships extends Zipmark_Pager {
  /**
   * Find all vendor relationships
   *
   * @param  string                         $params Parameters for find
   * @param  Zipmark_Client                 $client Client object to connect to service
   *
   * @return Zipmark_VendorRelationships            A list of Disbursements
   */
  public static function get($params = null, $client = null) {
    $list = new Zipmark_VendorRelationships(null, $client);
    $list->setHref($list->pathFor());
    $list->_loadFrom($list->pathFor(), $params);
    return $list;
  }
}

?>
