<?php

class Zipmark_Client extends Zipmark_Base {
  public static $appId;

  public static $appSecret;

  private static $sandboxApiUrl = 'https://sandbox.zipmark.com';
  private static $productionApiUrl = 'https://api.zipmark.com';

  private $_http;
  private $_appId;
  private $_appSecret;
  private $_production = false;
  private $_apiUrl;

  const GET  = 'GET';
  const POST = 'POST';
  const PUT  = 'PUT';
  
  // Paths within Zipmark, relative to base URL
  const PATH_APPROVAL_RULES       = '/approval_rules';
  const PATH_BILLS                = '/bills';
  const PATH_CALLBACKS            = '/callbacks';
  const PATH_DISBURSEMENTS        = '/disbursements';
  const PATH_VENDOR_RELATIONSHIPS = '/vendor_relationships';

  // Zipmark Object Types
  private static $zipmarkObjectTypes = array(
    'approval_rule', 'approval_rules',
    'bill', 'bills',
    'callback', 'callbacks',
    'disbursement', 'disbursements',
    'vendor',
    'vendor_relationship', 'vendor_relationships'
  );

  /**
   * Create a new Zipmark_Client
   *
   * @param string $appId     Application Identifier
   * @param string $appSecret Application Secret
   * @param string $apiUrl    URL of Zipmark API - Defaults to production
   */
  function __construct($appId = null, $appSecret = null, $production = false, $apiUrl = null, Zipmark_Http $http = null) {
    $this->_appId = $appId;
    $this->_appSecret = $appSecret;
    $this->_production = $production;
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

    // Objects
    foreach (self::$zipmarkObjectTypes as $obj) {
      $className = Zipmark_Base::getClassName($obj);
      $this->$obj = new $className(null, $this);
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
  public function request($method, $path, $data = null) {
    $response = $this->_http->$method($path, $data);
    $response->checkResponse();
    return $response;
  }

  /**
   * Current Application Identifier
   *
   * @return string Application Identifier
   */
  public function appId() {
    return (empty($this->_appId) ? Zipmark_Client::$appId : $this->_appId);
  }

  /**
   * Current Application Secret
   *
   * @return string Application Secret
   */
  public function appSecret() {
    return (empty($this->_appSecret) ? Zipmark_Client::$appSecret : $this->_appSecret);
  }

  /**
   * Current API URL
   *
   * @return string API URL
   */
  public function apiUrl() {
    if (empty($this->_apiUrl))
      return ($this->_production ? Zipmark_Client::$productionApiUrl : Zipmark_Client::$sandboxApiUrl);
    else
      return $this->_apiUrl;
  }

  /**
   * Enable/disable production mode
   *
   * @param boolean $enabled True/false to enable/disable production mode
   */
  public function setProduction($enabled) {
    $this->_production = $enabled;
    $this->_http->setApiUrl($this->apiUrl());
  }
}

?>
