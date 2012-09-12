<?php

class Zipmark_VendorRelationship extends Zipmark_Resource {
  /**
   * Find all vendor relationships
   *
   * @param  string                         $params Parameters for find
   * @param  Zipmark_Client                 $client Client object to connect to service
   *
   * @return Zipmark_VendorRelationshipList         A list of Disbursements
   */
  public static function all($params = null, $client = null) {
    $list = new Zipmark_VendorRelationshipList(Zipmark_Client::PATH_VENDOR_RELATIONSHIPS, $client);
    $list->_loadFrom(Zipmark_Client::PATH_VENDOR_RELATIONSHIPS, $params);
    return $list;
  }

  /**
   * Provide the object's name
   *
   * @return string The Object Name
   */
  public function getObjectName() {
    return 'vendor_relationship';
  }
}

?>
