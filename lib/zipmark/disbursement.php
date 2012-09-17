<?php

class Zipmark_Disbursement extends Zipmark_Resource {
  /**
   * Create a new Zipmark_Disbursement
   *
   * @param string  $user_email   Recipient's e-mail address
   * @param string  $customer_id  Unique Customer Identifier
   * @param integer $amount_cents Amount in cents
   */
  function __construct($user_email = null, $customer_id = null, $amount_cents = null) {
    if (!is_null($user_email))
      $this->user_email = $user_email;
    if (!is_null($customer_id))
      $this->customer_id = $customer_id;
    if (!is_null($amount_cents))
      $this->amount_cents = $amount_cents;
  }

  /**
   * Find all disbursements
   *
   * @param  string                   $params Parameters for find
   * @param  Zipmark_Client           $client Client object to connect to service
   *
   * @return Zipmark_Disbursements            A list of Disbursements
   */
  public static function all($params = null, $client = null) {
    $list = new Zipmark_Disbursements(Zipmark_Client::PATH_DISBURSEMENTS, $client);
    $list->_loadFrom(Zipmark_Client::PATH_DISBURSEMENTS, $params);
    return $list;
  }

  /**
   * Find a disbursement by its ID
   *
   * @param  string               $disbursementId Disbursement ID
   * @param  Zipmark_Client       $client         Client object to connect to service
   *
   * @return Zipmark_Disbursement                 The Disbursement
   */
  public static function get($disbursementId, $client = null) {
    return self::_get(self::pathForDisbursement($disbursementId), $client);
  }

  /**
   * Create a new Zipmark_Disbursement at Zipmark
   *
   * @param Zipmark_Client $client Client object to connect to service
   */
  public function create($client = null) {
    if (!is_null($client))
      $this->setClient($client);

    $this->_save(Zipmark_Client::POST, Zipmark_Client::PATH_DISBURSEMENTS);
  }

  /**
   * Update an existing Zipmark_Disbursement at Zipmark
   *
   * @param Zipmark_Client $client Client object to connect to service
   */
  public function update($client = null) {
    if (!is_null($client))
      $this->setClient($client);

    $this->_save(Zipmark_Client::PUT, $this->path());
  }

  protected static function pathForDisbursement($disbursementId) {
    return Zipmark_Client::PATH_DISBURSEMENTS . '/' . rawurlencode($disbursementId);
  }

  protected function path() {
    if (!empty($this->_href))
      return $this->getHref();
    else
      return self::pathForDisbursement($this->disbursementId);
  }
}

?>
