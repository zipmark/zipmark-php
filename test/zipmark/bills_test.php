<?php

class Zipmark_BillsTest extends UnitTestCase {
  public function testBillsGet() {
    $rootResponse = loadFixture('root_list.http');
    $response = loadFixture('bills/list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $response, array('http://example.org/bills', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills->get_all();

    $this->assertIsA($bills, 'Zipmark_Collection');
    $this->assertEqual($bills->getHref(), 'http://example.org/bills');
    $this->assertEqual($bills->count(), 22);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
