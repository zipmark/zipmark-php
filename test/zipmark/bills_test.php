<?php

class Zipmark_BillsTest extends UnitTestCase {
  public function testBillsGet() {
    $response = loadFixture('bills/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/bills'));

    $bills = Zipmark_Bills::get(null, $client);

    $this->assertIsA($bills, 'Zipmark_Bills');
    $this->assertEqual($bills->getHref(), '/bills');
    $this->assertEqual($bills->count(), 22);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
