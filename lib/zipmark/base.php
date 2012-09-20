<?php

abstract class Zipmark_Base {
  private $_name;
  private $_href;
  private $_client;
  private $_links;

  /**
   * Create a new Zipmark_Base
   *
   * @param string         $name   The object's name
   * @param string         $href   URL linking to the object
   * @param Zipmark_Client $client The object's client
   */
  function __construct($name = null, $href = null, $client = null) {
    $this->_name = $name;
    $this->_href = $href;
    $this->_client = $client;
    $this->_links = array();
  }

  /**
   * Provide the link to this object
   *
   * @return string The Object link
   */
  public function getHref() {
    return $this->_href;
  }

  /**
   * Set the link to this object
   *
   * @param string $href The Object link
   */
  public function setHref($href) {
    $this->_href = $href;
  }

  /**
   * Provide the object's client
   *
   * @return Zipmark_Client The object's client
   */
  public function getClient() {
    return $this->_client;
  }

  /**
   * Set the object's client
   *
   * @param Zipmark_Client $client The object's client
   */
  public function setClient($client) {
    $this->_client = $client;
  }

  /**
   * Provide the object's links
   *
   * @return array The object's links
   */
  public function getLinks() {
    return $this->_links;
  }

  /**
   * Set the object's links
   *
   * @param array $links The object's links
   */
  public function setLinks($links) {
    $this->_links = $links;
  }

  /**
   * GET the specified path, validate the response and return the resulting object
   *
   * @param string        $path   Relative path to the desired resource
   *
   * @return Zipmark_Base         A Zipmark Object of type TBD
   */
  public function _get($path) {
    $response = $this->_client->request(Zipmark_Client::GET, $path);
    return Zipmark_Base::_parseJsonToNewObject($response->body, $this->_client);
  }

  /**
   * Get the object name
   *
   * @return string The object name
   */
  public function getObjectName() {
    return $this->_name;
  }

  /**
   * Return an object URL.  If an object ID is provided, return the path to that object.
   *
   * @param string  $objId  The Object ID
   *
   * @return string         The Object's URL
   */
  public function pathFor($objId = null) {
    if (is_null($objId)) {
      return $this->getHref();
    } else {
      return $this->getHref() . '/' . rawurlencode($objId);
    }
  }

  /**
   * Build a new Zipmark_Resource
   *
   * @param array $values Associative array of object attributes
   */
  public function build($values = array()) {
    if (!$this instanceof Zipmark_Collection)
      return null;

    $collectionName = $this->getObjectName();
    $objectName = rtrim($collectionName, 's');
    $object = new Zipmark_Resource($objectName, $this->getHref(), $this->_client);
    
    foreach ($values as $k => $v) {
      $object->$k = $v;
    }
    return $object;
  }

  /**
   * Create a new Zipmark_Resource and save it to the Zipmark Service
   *
   * @param array $values Associative array of object attributes
   */
  public function create($values = array()) {
    $object = $this->build($values);
    return $object->save();
  }

  protected static function _parseJsonToNewObject($json, $client = null) {
    $parsedObject = json_decode($json, true);
    if (is_null($parsedObject)) return null;

    $obj = Zipmark_Base::_createObject($parsedObject);

    $obj->_client = $client;
    return $obj;
  }

  protected function _parseJsonToUpdateObject($json) {
    $parsedObject = json_decode($json, true);
    if (is_null($parsedObject)) return null;

    $objName = key($parsedObject);

    if ($objName == $this->getObjectName())
      // Update the current object
      Zipmark_Base::_buildObject($objName, $parsedObject[$objName], $this);
    else if ($objName == 'errors')
      // Add errors to existing object
      Zipmark_Base::_buildObject($objName, $parsedObject, $this->_errors);
  }

  private static function _createObject($parsedObject) {
    $objName = key($parsedObject);

    $href = Zipmark_Base::_findSelfHref($parsedObject);
    $newObj = new Zipmark_Resource($objName, $href);

    if (!empty($href))
      $newObj->setHref($href);
    else if ($newObj instanceof Zipmark_Collection) {
      $newObj->_count = Zipmark_Base::_numRecords($parsedObject);
    }

    self::_buildObject($objName, $parsedObject[$objName], $newObj);

    return $newObj;
  }

  protected static function _buildObject($objK, $objV, &$obj) {
    switch (gettype($objV)) {
      case 'array':
        foreach ($objV as $k => $v) {
          if (is_array($v) && array_key_exists('links', $v)) {
            $obj->$k = self::_createObject(array($k => $v));
          }
          else if ($k == "links") {
            $obj->$k = $v;
          } else {
            $obj = self::_buildObject($k, $v, $obj);
          }
        }
        break;
      case 'string':
      case 'boolean':
      case 'integer':
      case 'float':
        $obj->$objK = $objV;
        break;
    }

    if (isset($obj->_unsavedKeys))
      $obj->_unsavedKeys = array();

    return $obj;
  }

  protected static function _findSelfHref($parsedObject) {
    $objName = key($parsedObject);
    $links = $parsedObject[$objName]["links"];
    if (!is_null($links)) {
      foreach ($links as $link) {
        if ($link["rel"] == "self")
          return $link["href"];
      }
    }
    
    return null;
  }

  private static function _numRecords($parsedObject) {
    $meta = $parsedObject["meta"];
    $pagination = $meta["pagination"];
    return $pagination["total"];
  }
}

?>
