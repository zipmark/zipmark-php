<?php

class Zipmark_Bill extends Zipmark_Resource {
  /**
   * Create a new Zipmark_Bill
   *
   * @param string  $identifier       Unique Bill Identifier
   * @param integer $amount_cents     Amount in cents
   * @param uuid    $bill_template_id ID of the associated Bill Template
   * @param string  $memo             Bill Memo
   * @param string  $date             Origination date for the bill
   * @param string  $content          The Bill content, as JSON
   */
  function __construct($identifier = null, $amount_cents = null, $bill_template_id = null,
    $memo = null, $date = null, $content = null) {
    if (!is_null($identifier))
      $this->identifier = $identifier;
    if (!is_null($amount_cents))
      $this->amount_cents = $amount_cents;
    if (!is_null($bill_template_id))
      $this->bill_template_id = $bill_template_id;
    if (!is_null($memo))
      $this->memo = $memo;
    if (!is_null($date))
      $this->date = $date;
    if (!is_null($content))
      $this->content = $content;
  }

  /**
   * Find all bills
   *
   * @param  string                   $params Parameters for find
   * @param  Zipmark_Client           $client Client object to connect to service
   *
   * @return Zipmark_Bills                    A list of Approval Rules
   */
  public static function all($params = null, $client = null) {
    $list = new Zipmark_Bills(Zipmark_Client::PATH_BILLS, $client);
    $list->_loadFrom(Zipmark_Client::PATH_BILLS, $params);
    return $list;
  }

  /**
   * Find a bill by its ID
   *
   * @param  string         $billId Bill ID
   * @param  Zipmark_Client $client Client object to connect to service
   *
   * @return Zipmark_Bill           The Bill
   */
  public static function get($billId, $client = null) {
    return self::_get(self::pathForBill($billId), $client);
  }

  /**
   * Create a new Zipmark_Bill at Zipmark
   *
   * @param Zipmark_Client $client Client object to connect to service
   */
  public function create($client = null) {
    if (!is_null($client))
      $this->setClient($client);

    $this->_save(Zipmark_Client::POST, Zipmark_Client::PATH_BILLS);
  }

  /**
   * Update an existing Zipmark_Bill at Zipmark
   *
   * @param Zipmark_Client $client Client object to connect to service
   */
  public function update($client = null) {
    if (!is_null($client))
      $this->setClient($client);

    $this->_save(Zipmark_Client::PUT, $this->path());
  }

  protected static function pathForBill($billId) {
    return Zipmark_Client::PATH_BILLS . '/' . rawurlencode($billId);
  }

  protected function path() {
    if (!empty($this->_href))
      return $this->getHref();
    else
      return self::pathForBill($this->billId);
  }
}

?>
