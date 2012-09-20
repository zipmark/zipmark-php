<?php

class Zipmark_Http {
  var $_appId, $_appSecret, $_baseUri, $_curlOpts;
  
  // Zipmark API Version
  const API_VERSION    = '2';

  // Zipmark PHP Client Library Version
  const CLIENT_VERSION = '0.2';

  /**
   * Create a new Zipmark_Http
   *
   * @param string $baseUri   URL of Zipmark API
   * @param string $appId     Application Identifier
   * @param string $appSecret Application Secret
   */
  public function __construct($baseUri, $appId, $appSecret) {
    $this->_baseUri = $baseUri;
    $this->_appId = $appId;
    $this->_appSecret = $appSecret;
    $this->_curlOpts = array(
      CURLOPT_SSL_VERIFYPEER => TRUE,
      CURLOPT_SSL_VERIFYHOST => 2,
      CURLOPT_FOLLOWLOCATION => FALSE,
      CURLOPT_MAXREDIRS => 1,
      CURLOPT_HEADER => TRUE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT => 45,
      CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
      CURLOPT_USERPWD => $this->_appId . ':' . $this->_appSecret,
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Accept: application/vnd.com.zipmark.v' . self::API_VERSION . '+json',
        self::_userAgent()
      ),
    );
  }

  /**
   * Sends an HTTP request to the Zipmark API
   *
   * @param string                  $name The HTTP method for the request (e.g. GET, PUT, POST, etc)
   * @param array                   $args Path, Request Body (For PUT/POST only)
   *
   * @return Zipmark_ClientResponse       A Zipmark Response object
   *
   * @throws Zipmark_ConnectionError      If a cURL error occurs connecting to the Zipmark Service
   */
  public function __call($name, $args) {
    list($path, $req_body) = $args + array(0, '');

    if (substr($path, 0, 4) != 'http') 
      $uri = $this->_baseUri . $path;
    else
      $uri = $path;

    $opts = $this->_curlOpts;
    $opts[CURLOPT_URL] = $uri;

    switch ($name) {
      case 'get':
      case 'GET':
        $opts[CURLOPT_HTTPGET] = TRUE;
        break;
      case 'post':
      case 'POST':
        $opts[CURLOPT_CUSTOMREQUEST] = 'POST';
        $opts[CURLOPT_POSTFIELDS] = $req_body;
        break;
      case 'put':
      case 'PUT':
        $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
        $opts[CURLOPT_POSTFIELDS] = $req_body;
        break;
      default:
        $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($name);
        break;
    }

    try {
      if ($curl = curl_init()) {
        if (curl_setopt_array($curl, $opts)) {
          if ($response = curl_exec($curl)) {
            list($authHeader, $header, $body) = explode("\r\n\r\n", $response, 3);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);            
            $headers = $this->_getHeaders($header);
            curl_close($curl);
            return new Zipmark_ClientResponse($statusCode, $headers, $body);
          } else $this->_raiseCurlError(curl_errno($curl), curl_error($curl));
        } else $this->_raiseCurlError(curl_errno($curl), curl_error($curl));
      } else $this->_raiseCurlError(-1, "Unable to initialize cURL");
    } catch (ErrorException $e) {
      if (is_resource($curl)) curl_close($curl);
      throw $e;
    }
  }

  /**
   * Change the base URL
   *
   * @param string $url The base URL of the Zipmark API
   */
  public function setApiUrl($url) {
    $this->_baseUri = $url;
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
