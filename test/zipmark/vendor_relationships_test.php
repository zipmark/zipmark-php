<?php

class Zipmark_VendorRelationshipsTest extends UnitTestCase {
  public function testVendorRelationshipsGet() {
    $rootResponse = loadFixture('root_list.http');
    $response = loadFixture('vendor_relationships/list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $response, array('http://example.org/vendor_relationships', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $vendor_relationships = $client->vendor_relationships->get_all();

    $this->assertIsA($vendor_relationships, 'Zipmark_Collection');
    $this->assertEqual($vendor_relationships->getHref(), 'http://example.org/vendor_relationships');
    $this->assertEqual($vendor_relationships->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
