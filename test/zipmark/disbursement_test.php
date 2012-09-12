<?php

class ZipmarkDisbursementTest extends UnitTestCase {
  function testDisbursementAll() {
    $response = loadFixture('disbursements/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/disbursements'));

    $disbursements = Zipmark_Disbursement::all(null, $client);

    $this->assertIsA($disbursements, 'Zipmark_DisbursementList');
    $this->assertEqual($disbursements->getHref(), '/disbursements');
    $this->assertEqual($disbursements->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }

  function testDisbursementGet() {
    $response = loadFixture('disbursements/get.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/disbursements/b1a42f3e-bf21-4651-8ca4-d716593277db'));

    $disbursement = Zipmark_Disbursement::get('b1a42f3e-bf21-4651-8ca4-d716593277db', $client);

    $this->assertIsA($disbursement, 'Zipmark_Disbursement');
    $this->assertEqual($disbursement->getHref(), 'http://example.org/disbursements/b1a42f3e-bf21-4651-8ca4-d716593277db');
    $this->assertEqual($disbursement->id, 'b1a42f3e-bf21-4651-8ca4-d716593277db');
    $this->assertEqual($disbursement->customer_id, "Customer ID");
    $this->assertEqual($disbursement->status, 'pending');
    $this->assertEqual($disbursement->amount_cents, 5000);
  }

  function testDisbursementCreate() {
    $response = loadFixture('disbursements/create.http');

    $disbursement = new Zipmark_Disbursement();
    $disbursement->user_email = 'test@example.com';
    $disbursement->customer_id = 'abc123';
    $disbursement->amount_cents = 5000;
    
    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('POST', '/disbursements', $disbursement->toJson()));

    $disbursement->create($client);

    $this->assertIsA($disbursement, 'Zipmark_Disbursement');
    $this->assertEqual($disbursement->id, 'eafe7f6e-22b3-453e-9637-a482e2a144da');
    $this->assertEqual($disbursement->customer_id, 'abc123');
    $this->assertEqual($disbursement->status, 'pending');
    $this->assertEqual($disbursement->amount_cents, 5000);
  }

  function testDisbursementCreateFail() {
    $response = loadFixture('disbursements/create_fail.http');

    $disbursement = new Zipmark_Disbursement();
    $disbursement->user_email = 'test@example.com';
    $disbursement->customer_id = 'abc123';
    
    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('POST', '/disbursements', $disbursement->toJson()));

    try {
      $disbursement->create($client);
      $this->fail("Expected Zipmark_ValidationError");
    }
    catch (Zipmark_ValidationError $e) {
      $this->assertEqual($e->getMessage(), "disbursement - amount_cents: can't be blank");
      $this->pass("Received Zipmark_ValidationError");
    }

    $this->assertEqual($response->statusCode, 422);
  }

  function testDisbursementToJson() {
    $disbursement = new Zipmark_Disbursement();
    $disbursement->customer_id = 'abc123';
    $disbursement->amount_cents = 5000;
    
    $json = $disbursement->toJson();
    $this->assertEqual($json, '{"disbursement":{"customer_id":"abc123","amount_cents":5000}}');
  }
}

?>
