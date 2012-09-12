<?php

class Zipmark_VendorRelationshipListTest extends UnitTestCase {
  public function testVendorRelationshipListGet() {
    $response = loadFixture('vendor_relationships/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/vendor_relationships'));

    $vendor_relationships = Zipmark_VendorRelationshipList::get(null, $client);

    $this->assertIsA($vendor_relationships, 'Zipmark_VendorRelationshipList');
    $this->assertEqual($vendor_relationships->getHref(), '/vendor_relationships');
    $this->assertEqual($vendor_relationships->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
