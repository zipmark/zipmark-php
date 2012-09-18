<?php

class Zipmark_BillsTest extends UnitTestCase {
  public function testBillsGet() {
    $response = loadFixture('bills/list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $response, array('/bills', null));

    $client = new Zipmark_Client(null, null, false, null, $http);

    $bills = $client->bills->get();

    $this->assertIsA($bills, 'Zipmark_Bills');
    $this->assertEqual($bills->getHref(), '/bills');
    $this->assertEqual($bills->count(), 22);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
