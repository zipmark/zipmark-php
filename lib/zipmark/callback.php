<?php

class Zipmark_Callback
{
  private $_client;
  private $_phpRequest;
  private $_headers;
  private $_callbackFields = array('valid' => false);

  /**
   * Create a new Zipmark_Callback
   *
   * @param Zipmark_Client $client   The configured Zipmark_Client object
   * @param array          $headers  The array of HTTP headers sent in the callback POST ($_SERVER)
   * @param string         $body     The body of the callback POST (file_get_contents('php://input');)
   */
  public function __construct($client, $headers, $body)
  {
    $this->_client = $client;
    $this->_phpRequest['headers'] = $headers;
    $this->_phpRequest['body'] = $body;
    
    $this->_populateHeaders();
    $this->_parseBody();
    $this->_validate();
  }

  /**
   * Report whether the response provided is valid
   *
   * @return boolean The validity of the callback
   */
  public function isValid()
  {
    return $this->_callbackFields['valid'];
  }

  /**
   * The callback's event
   *
   * @return string The callback event (e.g. 'bill.create'), or null for an invalid callback
   */
  public function event()
  {
    return $this->isValid()
          ? $this->_callbackFields['event']
          : null;
  }

  /**
   * The callback's object type
   *
   * @return string The callback object type (e.g. 'bill'), or null for an invalid callback
   */
  public function objectType()
  {
    return $this->isValid()
          ? $this->_callbackFields['type']
          : null;
  }

  /**
   * The callback's object
   *
   * @return Zipmark_Resource The callback object, or null for an invalid callback
   */
  public function object()
  {
    return $this->isValid()
          ? $this->_callbackFields['object']
          : null;
  }

  private function _populateHeaders()
  {
    $this->_headers = array(
      'content_type'                     => $this->_phpRequest['headers']['HTTP_CONTENT_TYPE'],
      'accept'                           => $this->_phpRequest['headers']['HTTP_ACCEPT'],
      'x-zipmark-application-identifier' => $this->_phpRequest['headers']['HTTP_X_ZIPMARK_APPLICATION_IDENTIFIER'],
      'date'                             => $this->_phpRequest['headers']['HTTP_DATE'],
      'authorization'                    => $this->_phpRequest['headers']['HTTP_AUTHORIZATION'],
      'callback_path'                    => $this->_phpRequest['headers']['REQUEST_URI'],
    );
  }

  private function _parseBody()
  {
    $parsedBody = json_decode($this->_phpRequest['body'], true);
    if (is_array($parsedBody) && array_key_exists('callback', $parsedBody)) {
      $callback = $parsedBody['callback'];
      $this->_callbackFields['event'] = $callback['event'];
      $this->_callbackFields['type'] = strtolower($callback['object_type']);
      $objectArray = array($this->objectType() => $callback['object']);
      $this->_callbackFields['object'] = Zipmark_Resource::fromJson(json_encode($objectArray), $this->_client);
    } else {
      throw new Zipmark_Error("Body does not contain valid content.");
    }
  }

  private function _validate()
  {
    // Pull together the required data
    $date = $this->_headers['date'];
    $hashedContent = md5($this->_phpRequest['body']);
    $uriPath = $this->_headers['callback_path'];
    $appIdentifier = $this->_headers['x-zipmark-application-identifier'];

    // Generate the string that will be signed
    $stringToSign = "POST\n$hashedContent\napplication/json\n$date\n$uriPath\n$appIdentifier";

    // Sign the string
    $signedString = base64_encode(hash_hmac('sha1', $stringToSign, $this->_client->appSecret(), true));

    // Extract the signature from the provided headers
    $zipmarkAppIdSignature = ltrim($this->_headers['authorization']);
    list($zipmarkAppId, $zipmarkSignature) = explode(':', $zipmarkAppIdSignature);

    if ($signedString == $zipmarkSignature) {
      $this->_callbackFields['valid'] = true;
    }
  }
}

?>
