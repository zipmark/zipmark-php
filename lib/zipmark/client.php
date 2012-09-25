<?php

class Zipmark_Client extends Zipmark_Base
{
  public static $appId;

  public static $appSecret;

  private static $_sandboxApiUrl = 'https://sandbox.zipmark.com';
  private static $_productionApiUrl = 'https://api.zipmark.com';

  private $_http;
  private $_appId;
  private $_appSecret;
  private $_production = false;
  private $_apiUrl;
  private $_collections = array();
 
  /**
   * Create a new Zipmark_Client
   *
   * @param string       $appId     Application Identifier
   * @param string       $appSecret Application Secret
   * @param string       $apiUrl    URL of Zipmark API - Defaults to production
   * @param Zipmark_Http $http A Zipmark_Http object to use - makes testing possible
   */
  function __construct($appId = null, $appSecret = null, $apiUrl = null, Zipmark_Http $http = null)
  {
    $this->_appId = $appId;
    $this->_appSecret = $appSecret;
    $this->_apiUrl = $apiUrl;

    if (null === $http) {
      if (!in_array('curl', get_loaded_extensions())) {
        trigger_error(
          "The Zipmark PHP Client requires curl to function.\n".
          "Please visit http://php.net/manual/en/curl.installation.php\n".
          "for further infomation.\n",
          E_USER_WARNING
        );
      }
      $http = new Zipmark_Http(
        $this->apiUrl(),
        $this->appId(),
        $this->appSecret()
      );
    }
    $this->_http = $http;
  }

  /**
   * Magic to retrieve collection types from the Zipmark service and make them usable
   *
   * @param  string             $k The name of the collection desired
   *
   * @return Zipmark_Collection    The requested collection, if it exists, or null
   */
  public function __get($k) 
  {
    // If the requested collection is known, return a new collection
    if (array_key_exists($k, $this->_collections)) {
      return new Zipmark_Collection($k, $this->_collections[$k], $this);
    } else {
      // Otherwise, load the collections and check again
      $this->_loadRoot();
      if (array_key_exists($k, $this->_collections)) {
        return new Zipmark_Collection($k, $this->_collections[$k], $this);
      } else {
        // If it still cannot be located, return null
        return null;
      }
    }
  }

  /**
   * Make a request to the Zipmark service and validate the response
   *
   * @param string                  $method The type of request to make (HTTP Verb)
   * @param string                  $path   Relative or absolute path of the request
   * @param json                    $data   JSON data to be sent
   *
   * @return Zipmark_ClientResponse         A Zipmark Response object
   */  
  public function request($method, $path, $data = null)
  {
    $response = $this->_http->$method($path, $data);
    $response->checkResponse();
    return $response;
  }

  /**
   * Current Application Identifier
   *
   * @return string Application Identifier
   */
  public function appId()
  {
    return empty($this->_appId)
          ? Zipmark_Client::$appId
          : $this->_appId;
  }

  /**
   * Current Application Secret
   *
   * @return string Application Secret
   */
  public function appSecret()
  {
    return empty($this->_appSecret)
          ? Zipmark_Client::$appSecret
          : $this->_appSecret;
  }

  /**
   * Current API URL
   *
   * @return string API URL
   */
  public function apiUrl()
  {
    if (empty($this->_apiUrl)) {
      return $this->_production
            ? Zipmark_Client::$_productionApiUrl
            : Zipmark_Client::$_sandboxApiUrl;
    } else {
      return $this->_apiUrl;
    }
  }

  /**
   * Enable/disable production mode
   *
   * @param boolean $enabled True/false to enable/disable production mode
   */
  public function setProduction($enabled)
  {
    $this->_production = $enabled;
    $this->_http->setApiUrl($this->apiUrl());
  }

  /**
   * Return an array of available resources
   *
   * @return array Available resources from Zipmark service
   */
  public function resources()
  {
    if (!count($this->_collections)) {
      $this->_loadRoot();
    }

    return array_keys($this->_collections);
  }

  private function _loadRoot()
  {
    $response = $this->request('GET', '/');
    
    $parsedObject = json_decode($response->body, true);
    if (is_null($parsedObject)) {
      return null;
    }

    if (array_key_exists('vendor_root', $parsedObject)) {
      if (array_key_exists('links', $parsedObject['vendor_root'])) {
        foreach ($parsedObject['vendor_root']['links'] as $link) {
          $this->_collections[$link['rel']] = $link['href'];
        }
      } else throw new Zipmark_Error("Root response does not contain links");
    } else throw new Zipmark_Error("Root response does not contain vendor_root");
  }
}

?>
