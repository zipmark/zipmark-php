<?php

/**
 * Exception classes used by the Zipmark PHP Client.
 *
 */
class Zipmark_Error extends Exception {}

class Zipmark_NotFoundError extends Zipmark_Error {}

class Zipmark_UnauthorizedError extends Zipmark_Error {}

class Zipmark_ConnectionError extends Zipmark_Error {}

class Zipmark_RequestError extends Zipmark_Error {}

class Zipmark_ServerError extends Zipmark_Error {}

class Zipmark_ValidationError extends Zipmark_Error {
  var $object;
  var $errors;
  
  function __construct($object, $errors) {
    $this->object = $object;

    $message = $object->getObjectName();
    $errAry = get_object_vars($errors);
    if (count($errAry) > 0) {
      $message .= " - ";
      $numErrs = 0;
    }
    foreach ($errors as $k => $v) {
      $numErrs++;
      if ($numErrs > 1)
        $message .= ", ";
      $this->errors[$k] = $v[0];
      $message .= $k . ": " . $v[0];
    }

    parent::__construct($message);
  }
}

?>
