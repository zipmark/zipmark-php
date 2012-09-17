<?php

class Zipmark_Client {
  public static $appId;

  public static $appSecret;

  private static $sandboxApiUrl = 'https://sandbox.zipmark.com';
  private static $productionApiUrl = 'https://api.zipmark.com';

  private $_appId;
  private $_appSecret;
  private $_production = false;
  private $_apiUrl;

  // Zipmark API Version
  const API_VERSION    = '2';

  // Zipmark PHP Client Library Version
  const CLIENT_VERSION = '0.1';

  const GET  = 'GET';
  const POST = 'POST';
  const PUT  = 'PUT';
  
  // Paths within Zipmark, relative to base URL
  const PATH_APPROVAL_RULES       = '/approval_rules';
  const PATH_BILLS                = '/bills';
  const PATH_CALLBACKS            = '/callbacks';
  const PATH_DISBURSEMENTS        = '/disbursements';
  const PATH_VENDOR_RELATIONSHIPS = '/vendor_relationships';

  /**
   * Create a new Zipmark_Client
   *
   * @param string $appId     Application Identifier
   * @param string $appSecret Application Secret
   * @param string $apiUrl    URL of Zipmark API - Defaults to production
   */
  function __construct($appId = null, $appSecret = null, $production = false, $apiUrl = null) {
    $this->_appId = $appId;
    $this->_appSecret = $appSecret;
    $this->_production = $production;
    $this->_apiUrl = $apiUrl;
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
    $response = $this->_sendRequest($method, $path, $data);
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
      return ($this->_production) ? Zipmark_Client::$productionApiUrl : Zipmark_Client::$sandboxApiUrl;
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
  }

  /**
   * Sends an HTTP request to the Zipmark API
   *
   * @param string                  $method The HTTP method for the request (e.g. GET, PUT, POST, etc)
   * @param string                  $path   Absolute or Relative Path for the request
   * @param json                    $data   JSON data to be sent (PUT or POST only)
   *
   * @return Zipmark_ClientResponse         A Zipmark Response object
   */
  private function _sendRequest($method, $path, $data = '') {
    if (substr($path, 0, 4) != 'http') 
      $uri = $this->apiUrl() . $path;
    else
      $uri = $path;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uri);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
    curl_setopt($ch, CURLOPT_USERPWD, $this->appId() . ':' . $this->appSecret());
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json',
      'Accept: application/vnd.com.zipmark.v' . self::API_VERSION . '+json',
      Zipmark_Client::_userAgent()
    ));
    
    if ('POST' == $method) {
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    else if ('PUT' == $method) {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    else if('GET' != $method) {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }

    $response = curl_exec($ch);

    if ($response === false) {
      $errorNumber = curl_errno($ch);
      $message = curl_error($ch);
      curl_close($ch);
      $this->_raiseCurlError($errorNumber, $message);
    }

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    print $uri;
    print $response;

    /* Layout of a valid response is:
     * Auth challenge response headers
     * Headers after HTTP Digest Auth
     * Body
     */
    list($authHeader, $header, $body) = explode("\r\n\r\n", $response, 3);
    $headers = $this->_getHeaders($header);

    return new Zipmark_ClientResponse($statusCode, $headers, $body);
  }

  private static function _userAgent() {
    return 'User-Agent: Zipmark/' . self::CLIENT_VERSION . '; PHP ' . phpversion() . ' [' . php_uname('s') . ']';
  }

  private function _raiseCurlError($errorNumber, $message) {
    switch ($errorNumber) {
      case CURLE_COULDNT_CONNECT:
      case CURLE_COULDNT_RESOLVE_HOST:
      case CURLE_OPERATION_TIMEOUTED:
        throw new Zipmark_ConnectionError("Failed to connect to Zipmark.");
      case CURLE_SSL_CACERT:
      case CURLE_SSL_PEER_CERTIFICATE:
        throw new Zipmark_ConnectionError("Could not verify SSL certificate from Zipmark service.");
      default:
        throw new Zipmark_ConnectionError("An unexpected error occurred connecting to Zipmark service.");
    }
  }

  private function _getHeaders($headerText) {
    $headers = explode("\r\n", $headerText);
    $returnHeaders = array();
    foreach ($headers as &$header) {
      preg_match('/([^:]+): (.*)/', $header, $matches);
      if (sizeof($matches) > 2)
        $returnHeaders[$matches[1]] = $matches[2];
    }
    return $returnHeaders;
  }
}

?>
