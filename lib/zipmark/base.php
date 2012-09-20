<?php

abstract class Zipmark_Base {
  protected $_href;
  protected $_client;
  protected $_links;

  /**
   * Create a new Zipmark_Base
   *
   * @param string         $href   URL linking to the object
   * @param Zipmark_Client $client The object's client
   */
  function __construct($href = null, $client = null) {
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
   * GET the specified path, validate the response and return the resulting object
   *
   * @param string        $path   Relative path to the desired resource
   *
   * @return Zipmark_Base         A Zipmark Object of type TBD
   */
  public function _get($path) {
    $response = $this->_client->request(Zipmark_Client::GET, $path);
    $response->checkResponse();
    return Zipmark_Base::_parseJsonToNewObject($response->body, $this->_client);
  }

  /**
   * Map and validate JSON object names to PHP class names
   *
   * Ex: bill -> Zipmark_Bill
   *
   * @param string $objectName The object name from JSON API return
   *
   * @return mixed             The PHP Class Name (if valid)
   *                           null (if invalid)
   */
  public static function getClassName($objectName) {
    $objectClass = "Zipmark_" . self::camelize($objectName);

    if (class_exists($objectClass))
      return $objectClass;
    else
      return null;
  }

  /**
   * Get the object name from the class name
   *
   * Ex: Zipmark_Bill -> Bill
   *     Zipmark_VendorRelationships -> VendorRelationships
   *
   * @param boolean $camelized   Whether to return camel case or not
   * @param string  $object_name Class name to convert to object name
   */
  public function getObjectName($camelized = false, $className = null) {
    if ($className)
      $name = $className;
    else
      $name = get_class($this);
    $parts = explode('_', $name);
    $resourceName = end($parts);
    if ($camelized) {
      return $resourceName;
    } else {
      return self::decamelize($resourceName);
    }
  }

  /**
   * Decamelize a string
   *
   * Ex: TheStringToDecamelize -> the_string_to_decamelize
   *
   * @param string $word The string to be decamelized
   */
  public static function decamelize($word) {
    return preg_replace(
      '/(^|[a-z])([A-Z])/e',
      'strtolower(strlen("\\1") ? "\\1_\\2" : "\\2")',
      $word
    );
  }

  /**
   * Camelize a string
   *
   * Ex: the_string_to_camelize -> TheStringToCamelize
   *
   * @param string $word The string to be camelized
   */
  public static function camelize($word) {
    return preg_replace(
      '/(^|_)([a-z])/e',
      'strtoupper("\\2")',
      $word
    );
  }

  /**
   * Return an object URL.  If an object ID is provided, return the path to that object.
   *
   * @param string  $objId  The Object ID
   *
   * @return string         The Object's URL
   */
  public function pathFor($objId = null) {
    $objPath = "PATH_" . strtoupper($this->getObjectName());
    if (is_null($objId)) {
      return constant("Zipmark_Client::$objPath");
    } else {
      $objPath .= "S";
      $path = constant("Zipmark_Client::$objPath") . '/' . rawurlencode($objId);
      return rtrim($path,'/');
    }
  }

  /**
   * Build a new Zipmark_EditableResource
   *
   * @param array $values Associative array of object attributes
   */
  public function build($values = array()) {
    if (!$this instanceof Zipmark_Collection)
      return null;

    $objName = $this->getObjectName();
    $classType = Zipmark_Base::getClassName(rtrim($objName, 's'));
    $obj = new $classType(null, $this->_client);

    foreach ($values as $k => $v) {
      $obj->$k = $v;
    }
    return $obj;
  }

  /**
   * Create a new Zipmark_EditableResource and save it to the Zipmark Service
   *
   * @param array $values Associative array of object attributes
   */
  public function create($values = array()) {
    $obj = $this->build($values);
    return $obj->save();
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
    $objClass = self::getClassName($objName);

    if (!$objClass)
      return new Zipmark_Object();

    $newObj = new $objClass();

    $href = Zipmark_Base::_findSelfHref($parsedObject);
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
          $objClass = self::getClassName($k);
          if ($objClass)
            $obj->$k = self::_createObject(array($k => $v));
          else if ($k == "links")
            $obj->$k = $v;
          else
            $obj = self::_buildObject($k, $v, $obj);
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

// In case objClass is not specified
class Zipmark_Object {}

?>
