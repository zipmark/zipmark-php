<?php

class Zipmark_DisbursementList extends Zipmark_Pager {
  /**
   * Find all disbursements
   *
   * @param  string                   $params Parameters for find
   * @param  Zipmark_Client           $client Client object to connect to service
   *
   * @return Zipmark_DisbursementList         A list of Disbursements
   */
  public static function get($params = null, $client = null) {
    $list = new Zipmark_DisbursementList(Zipmark_Client::PATH_DISBURSEMENTS, $client);
    $list->_loadFrom(Zipmark_Client::PATH_DISBURSEMENTS, $params);
    return $list;
  }

  /**
   * Provide the object's name
   *
   * @return string The Object Name
   */
  public function getObjectName() {
    return 'disbursements';
  }
}

?>
