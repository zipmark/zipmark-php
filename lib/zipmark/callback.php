<?php

class Zipmark_Callback extends Zipmark_Resource {
  /**
   * Create a new Zipmark_Callback
   *
   * @param string $url   URL to be contacted by the callback
   * @param string $event Event the callback should act upon
   */
  function __construct($url = null, $event = null) {
    if (!is_null($url))
      $this->url = $url;
    if (!is_null($event))
      $this->event = $event;
  }

  /**
   * Find all callbacks
   *
   * @param  string               $params Parameters for find
   * @param  Zipmark_Client       $client Client object to connect to service
   *
   * @return Zipmark_Callbacks            A list of Callbacks
   */
  public static function all($params = null, $client = null) {
    $list = new Zipmark_Callbacks(Zipmark_Client::PATH_CALLBACKS, $client);
    $list->_loadFrom(Zipmark_Client::PATH_CALLBACKS, $params);
    return $list;
  }

  /**
   * Find a callback by its ID
   *
   * @param  string           $callbackId Callback ID
   * @param  Zipmark_Client   $client     Client object to connect to service
   *
   * @return Zipmark_Callback             The Callback
   */
  public static function get($callbackId, $client = null) {
    return self::_get(self::pathForCallback($callbackId), $client);
  }

  /**
   * Create a new Zipmark_Callback at Zipmark
   *
   * @param Zipmark_Client $client Client object to connect to service
   */
  public function create($client = null) {
    if (!is_null($client))
      $this->setClient($client);

    $this->_save(Zipmark_Client::POST, Zipmark_Client::PATH_CALLBACKS);
  }

  /**
   * Update an existing Zipmark_Callback at Zipmark
   *
   * @param Zipmark_Client $client Client object to connect to service
   */
  public function update($client = null) {
    if (!is_null($client))
      $this->setClient($client);

    $this->_save(Zipmark_Client::PUT, $this->path());
  }

  protected static function pathForCallback($callbackId) {
    return Zipmark_Client::PATH_CALLBACKS . '/' . rawurlencode($callbackId);
  }

  protected function path() {
    if (!empty($this->_href))
      return $this->getHref();
    else
      return self::pathForCallback($this->callbackId);
  }
}

?>
