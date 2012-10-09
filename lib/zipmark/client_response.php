<?php

class Zipmark_ClientResponse
{
  public $statusCode;
  public $headers;
  public $body;

  /**
   * Create a new Zipmark_ClientResponse
   *
   * @param integer $statusCode Status code return from remote service
   * @param array   $headers    Headers
   * @param string  $body       Response body
   */
  function __construct($statusCode, $headers, $body)
  {
    $this->statusCode = $statusCode;
    $this->headers = $headers;
    $this->body = $body;
  }

  /**
   * Assess the success of a response
   *
   * @param  Zipmark_Base              $object Optional object to include with error
   *
   * @throws Zipmark_ConnectionError           If unable to connect to the Zipmark Service
   * @throws Zipmark_Error                     If a bad API request is made
   * @throws Zipmark_UnauthorizedError         If lacking or improper credentials are provided
   * @throws Zipmark_NotFoundError             If the object requested doesn't exist
   * @throws Zipmark_ValidationError           If the object fails validation on create or update
   * @throws Zipmark_ServerError               If an internal server error occurs at Zipmark
   * @throws Zipmark_RequestError              If an unknown 4XX error occurs
   */
  public function checkResponse($object = null)
  {
    // Successful response code
    if ($this->statusCode >= 200 && $this->statusCode < 400)
      return;

    // Do not fail here if the response is not valid JSON
    $error = @$this->_parseErrorJson($this->body);

    switch ($this->statusCode) {
    case 0:
      throw new Zipmark_ConnectionError('An error occurred while connecting to Zipmark.');
    case 400:
      $message = (is_null($error) ? 'Bad API Request' : implode(": ", $error));
      throw new Zipmark_Error($message);
    case 401:
      throw new Zipmark_UnauthorizedError('Your credentials are not authorized to connect to Zipmark.');
    case 403:
      throw new Zipmark_UnauthorizedError('Please use an API key to connect to Zipmark.');
    case 404:
      $message = is_null($error)
                ? 'Object not found'
                : $error;
      throw new Zipmark_NotFoundError($message);
    case 422:
      if (isset($object)) {
        $errors = json_decode($this->body);
        // Handle old and new style errors
        if (isset($errors->errors)) {
          throw new Zipmark_ValidationError($object, $errors->errors);
        } else {
          throw new Zipmark_ValidationError($object, $errors);
        }
      }
      return;
    case 500:
      $message = is_null($error) 
                ? 'An error occurred while connecting to Zipmark' 
                : 'An error occurred while connecting to Zipmark: ' . implode(": ", $error);
      throw new Zipmark_ServerError($message);
    case 502:
    case 503:
    case 504:
      throw new Zipmark_ConnectionError('An error occurred while connecting to Zipmark.');
    }

    // Catch future 400-499 errors as request errors
    if ($this->statusCode >= 400 && $this->statusCode < 500) {
      throw new Zipmark_RequestError("Invalid request, status code: {$this->statusCode}");
    }

    // Catch future 500-599 errors as server errors
    if ($this->statusCode >= 500 && $this->statusCode < 600) {
      $message = is_null($error)
                ? 'An error occurred while connecting to Zipmark'
                : 'An error occurred while connecting to Zipmark: ' . $error;
      throw new Zipmark_ServerError($message);
    }
  }

  private function _parseErrorJson($json)
  {
    $parsedResponse = json_decode($json, true);

    return is_null($parsedResponse) ? null : $parsedResponse["error"];
  }
}

?>
