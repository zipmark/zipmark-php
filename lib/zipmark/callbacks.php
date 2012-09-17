<?php

class Zipmark_Callbacks extends Zipmark_Pager {
  /**
   * Find all callbacks
   *
   * @param  string               $params Parameters for find
   * @param  Zipmark_Client       $client Client object to connect to service
   *
   * @return Zipmark_Callbacks            A list of Callbacks
   */
  public static function get($params = null, $client = null) {
    $list = new Zipmark_Callbacks(null, $client);
    $list->setHref($list->pathFor());
    $list->_loadFrom($list->pathFor(), $params);
    return $list;
  }
}

?>
