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
   * @param string        $client Optional client for the request, useful for mocking the client
   *
   * @return Zipmark_Base         A Zipmark Object of type TBD
   */
  public static function _get($path, $client = null) {
    if (is_null($client))
      $client = new Zipmark_Client();

    $response = $client->request(Zipmark_Client::GET, $path);
    $response->checkResponse();
    return Zipmark_Base::_parseJsonToNewObject($response->body, $client);
  }

  /**
   * POST to the specified path, validate the response and return the resulting object
   *
   * @param string        $path   Relative path to the desired resource
   * @param string        $data   Data to post
   * @param string        $client Optional client for the request, useful for mocking the client
   *
   * @return Zipmark_Base         A Zipmark Object of type TBD
   */
  public static function _post($path, $data = null, $client = null) {
    if (is_null($client))
      $client = new Zipmark_Client();

    $response = $client->request(Zipmark_Client::POST, $path, $data);
    $response->checkResponse();
    $object = Zipmark_Base::_parseJsonToNewObject($response->body, $client);
    $response->checkResponse($object);
    return $object;
  }

  /**
   * PUT to the specified path, validate the response and return the resulting object
   *
   * @param string        $path   Relative path to the desired resource
   * @param string        $data   Data to put
   * @param string        $client Optional client for the request, useful for mocking the client
   *
   * @return Zipmark_Base         A Zipmark Object of type TBD
   */
  public static function _put($path, $data = null, $client = null) {
    if (is_null($client))
      $client = new Zipmark_Client();

    $response = $client->request(Zipmark_Client::PUT, $path, $data);
    $response->checkResponse();
    $object = Zipmark_Base::_parseJsonToNewObject($response->body, $client);
    $response->checkResponse($object);
    return $object;
  }

  /**
   * Mapping from Zipmark class types to PHP classes
   */
  static $classMap = array(
    'approval_rules'       => 'Zipmark_ApprovalRuleList',
    'approval_rule'        => 'Zipmark_ApprovalRule',
    'bills'                => 'Zipmark_BillList',
    'bill'                 => 'Zipmark_Bill',
    'callbacks'            => 'Zipmark_CallbackList',
    'callback'             => 'Zipmark_Callback',
    'disbursements'        => 'Zipmark_DisbursmentList',
    'disbursement'         => 'Zipmark_Disbursement',
    'vendor'               => 'Zipmark_Vendor',
    'vendor_relationships' => 'Zipmark_VendorRelationshipList',
    'vendor_relationship'  => 'Zipmark_VendorRelationship',
  );

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

    if (!array_key_exists($objName, Zipmark_Base::$classMap)) {
      return null; // Unknown element
    }

    $objClass = Zipmark_Base::$classMap[$objName];

    if ($objClass == null)
      return new Zipmark_Object();
    else if ($objClass == 'array')
      return array();
    else
      $newObj = new $objClass();

    $href = Zipmark_Base::_findSelfHref($parsedObject);
    if (!empty($href))
      $newObj->setHref($href);
    else if ($newObj instanceof Zipmark_Pager) {
      $newObj->_count = Zipmark_Base::_numRecords($parsedObject);
    }

    self::_buildObject($objName, $parsedObject[$objName], $newObj);

    return $newObj;
  }

  protected static function _buildObject($objK, $objV, &$obj) {
    switch (gettype($objV)) {
      case 'array':
        foreach ($objV as $k => $v) {
          if (array_key_exists($k, Zipmark_Base::$classMap))
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
