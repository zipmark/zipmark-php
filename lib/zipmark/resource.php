<?php

abstract class Zipmark_Resource extends Zipmark_Base {
  protected $_values;

  /**
   * Create a new Zipmark_Resource
   *
   * @param string         $href   URL linking to the object
   * @param Zipmark_Client $client The object's client
   */
  public function __construct($href = null, $client = null) {
    parent::__construct($href, $client);
    $this->_values = array();
  }

  /**
   * Add a key/value pair to the object
   *
   * @param string $k The key
   * @param object $v The value (can be anything: string, array, object, etc)
   */
  public function __set($k, $v) {
    $this->_values[$k] = $v;
  }

  /**
   * Determine if a key is set
   *
   * @param  string   $k The key
   *
   * @return boolean     Whether the specified key has a corresponding value or is null
   */
  public function __isset($k) {
    return isset($this->_values[$k]);
  }

  /**
   * Delete a key/value pair from the object
   *
   * @param string $k The key
   */
  public function __unset($k) {
    unset($this->_values[$k]);
  }

  /**
   * Retrieve a value from the object
   *
   * @param  string  $k The key
   *
   * @return object     The value (can be anything: string, array, object, etc)
   */
  public function &__get($k) {
    if (isset($this->_values[$k]))
      return $this->_values[$k];
    else
      return null;
  }

  /**
   * Find an object by its ID
   *
   * @param  string               $objectId The Object ID
   *
   * @return Zipmark_Resource               The Object
   */
  public function get($objectId) {
    return $this->_get($this->pathFor($objectId));
  }

  protected function path() {
    if (!empty($this->_href))
      return $this->getHref();
    else
      return $this->pathFor($this->id);
  }

  /**
   * Generate a JSON representation of the object
   *
   * @return string JSON representation of the object
   */
  public function toJson() {
    return json_encode(array($this->getObjectName() => $this->_values));
  }
}

?>
