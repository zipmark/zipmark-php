<?php

class Zipmark_CallbackList extends Zipmark_Pager {
  /**
   * Find all callbacks
   *
   * @param  string               $params Parameters for find
   * @param  Zipmark_Client       $client Client object to connect to service
   *
   * @return Zipmark_CallbackList         A list of Callbacks
   */
  public static function get($params = null, $client = null) {
    $list = new Zipmark_CallbackList(Zipmark_Client::PATH_CALLBACKS, $client);
    $list->_loadFrom(Zipmark_Client::PATH_CALLBACKS, $params);
    return $list;
  }

  /**
   * Provide the object's name
   *
   * @return string The Object Name
   */
  public function getObjectName() {
    return 'callbacks';
  }
}

?>
