<?php

class Zipmark_VendorRelationship extends Zipmark_Resource {
  /**
   * Find all vendor relationships
   *
   * @param  string                         $params Parameters for find
   * @param  Zipmark_Client                 $client Client object to connect to service
   *
   * @return Zipmark_VendorRelationships            A list of Disbursements
   */
  public static function all($params = null, $client = null) {
    return Zipmark_VendorRelationships::get($params, $client);
  }
}

?>
