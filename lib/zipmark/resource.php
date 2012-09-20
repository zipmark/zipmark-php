<?php

class Zipmark_Resource extends Zipmark_Base
{
  private $_values;

  /**
   * Create a new Zipmark_Resource
   *
   * @param string         $href   URL linking to the object
   * @param Zipmark_Client $client The object's client
   */
  public function __construct($name, $href, $client = null)
  {
    parent::__construct($name, $href, $client);
    $this->_values = array();
  }

  /**
   * Add a key/value pair to the object
   *
   * @param string $k The key
   * @param object $v The value (can be anything: string, array, object, etc)
   */
  public function __set($k, $v)
  {
    $this->_values[$k] = $v;
  }

  /**
   * Determine if a key is set
   *
   * @param  string   $k The key
   *
   * @return boolean     Whether the specified key has a corresponding value or is null
   */
  public function __isset($k)
  {
    return isset($this->_values[$k]);
  }

  /**
   * Delete a key/value pair from the object
   *
   * @param string $k The key
   */
  public function __unset($k)
  {
    unset($this->_values[$k]);
  }

  /**
   * Retrieve a value from the object
   *
   * @param  string  $k The key
   *
   * @return object     The value (can be anything: string, array, object, etc)
   */
  public function __get($k)
  {
    if (isset($this->_values[$k])) {
      return $this->_values[$k];
    } else {
      return null;
    }
  }

  /**
   * Find an object by its ID
   *
   * @param  string               $objectId The Object ID
   *
   * @return Zipmark_Resource               The Object
   */
  public function get($objectId)
  {
    $response = $this->getClient()->request('GET', $this->pathFor($objectId));
    return Zipmark_Base::parseJsonToNewObject($response->body, $this->_client);
  }

  /**
   * Generate a JSON representation of the object
   *
   * @return string JSON representation of the object
   */
  public function toJson()
  {
    return json_encode(array($this->getObjectName() => $this->_values));
  }

  /**
   * Save a Zipmark_Resource to the Zipmark Service
   *
   * @return Zipmark_Resource The Zipmark Object
   */
  public function save()
  {
    if ($this->id) {
      $response = $this->getClient()->request('PUT', $this->pathFor($this->id), $this->toJson());
    } else {
      $response = $this->getClient()->request('POST', $this->pathFor(), $this->toJson());
    }
    Zipmark_Base::parseJsonToUpdateObject($response->body);
    $response->checkResponse($this);

    return $this;
  }
}

?>
