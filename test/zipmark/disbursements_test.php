<?php

class Zipmark_DisbursementsTest extends UnitTestCase {
  public function testDisbursementsGet() {
    $response = loadFixture('disbursements/list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $response, array('/disbursements', null));

    $client = new Zipmark_Client(null, null, false, null, $http);

    $disbursements = $client->disbursements->get();

    $this->assertIsA($disbursements, 'Zipmark_Disbursements');
    $this->assertEqual($disbursements->getHref(), '/disbursements');
    $this->assertEqual($disbursements->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
