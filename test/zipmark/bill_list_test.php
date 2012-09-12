<?php

class Zipmark_BillListTest extends UnitTestCase {
  public function testBillListGet() {
    $response = loadFixture('bills/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/bills'));

    $bills = Zipmark_BillList::get(null, $client);

    $this->assertIsA($bills, 'Zipmark_BillList');
    $this->assertEqual($bills->getHref(), '/bills');
    $this->assertEqual($bills->count(), 22);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
