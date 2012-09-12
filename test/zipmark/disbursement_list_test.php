<?php

class Zipmark_DisbursementListTest extends UnitTestCase {
  public function testDisbursementListGet() {
    $response = loadFixture('disbursements/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/disbursements'));

    $disbursements = Zipmark_DisbursementList::get(null, $client);

    $this->assertIsA($disbursements, 'Zipmark_DisbursementList');
    $this->assertEqual($disbursements->getHref(), '/disbursements');
    $this->assertEqual($disbursements->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
