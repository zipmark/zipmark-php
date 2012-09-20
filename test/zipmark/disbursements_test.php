<?php

class Zipmark_DisbursementsTest extends UnitTestCase
{
  public function testDisbursementsGet()
  {
    $rootResponse = loadFixture('root_list.http');
    $response = loadFixture('disbursements/list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $response, array('http://example.org/disbursements', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $disbursements = $client->disbursements->getAll();

    $this->assertIsA($disbursements, 'Zipmark_Collection');
    $this->assertEqual($disbursements->getHref(), 'http://example.org/disbursements');
    $this->assertEqual($disbursements->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
