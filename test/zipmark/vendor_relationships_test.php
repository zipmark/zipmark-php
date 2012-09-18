<?php

class Zipmark_VendorRelationshipsTest extends UnitTestCase {
  public function testVendorRelationshipsGet() {
    $response = loadFixture('vendor_relationships/list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $response, array('/vendor_relationships', null));

    $client = new Zipmark_Client(null, null, false, null, $http);

    $vendor_relationships = $client->vendor_relationships->get_all();

    $this->assertIsA($vendor_relationships, 'Zipmark_VendorRelationships');
    $this->assertEqual($vendor_relationships->getHref(), '/vendor_relationships');
    $this->assertEqual($vendor_relationships->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
