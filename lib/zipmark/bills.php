<?php

class Zipmark_Bills extends Zipmark_Pager {
  /**
   * Find all bills
   *
   * @param  string           $params Parameters for find
   * @param  Zipmark_Client   $client Client object to connect to service
   *
   * @return Zipmark_Bills            A list of Bills
   */
  public static function get($params = null, $client = null) {
    $list = new Zipmark_Bills(Zipmark_Client::PATH_BILLS, $client);
    $list->_loadFrom(Zipmark_Client::PATH_BILLS, $params);
    return $list;
  }
}

?>
