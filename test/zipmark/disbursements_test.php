<?php

class Zipmark_DisbursementsTest extends UnitTestCase {
  public function testDisbursementsGet() {
    $response = loadFixture('disbursements/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/disbursements'));

    $disbursements = Zipmark_Disbursements::get(null, $client);

    $this->assertIsA($disbursements, 'Zipmark_Disbursements');
    $this->assertEqual($disbursements->getHref(), '/disbursements');
    $this->assertEqual($disbursements->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
