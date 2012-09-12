<?php

class ZipmarkBillTest extends UnitTestCase {
  function testBillAll() {
    $response = loadFixture('bills/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/bills'));

    $bills = Zipmark_Bill::all(null, $client);

    $this->assertIsA($bills, 'Zipmark_BillList');
    $this->assertEqual($bills->getHref(), '/bills');
    $this->assertEqual($bills->count(), 22);
    $this->assertEqual($response->statusCode, 200);
  }

  function testBillGet() {
    $response = loadFixture('bills/get.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/bills/3caca1e0a68fa94d5bf073fdfc1ef9db2a1b'));

    $bill = Zipmark_Bill::get('3caca1e0a68fa94d5bf073fdfc1ef9db2a1b', $client);

    $this->assertIsA($bill, 'Zipmark_Bill');
    $this->assertEqual($bill->getHref(), 'http://example.org/bills/3caca1e0a68fa94d5bf073fdfc1ef9db2a1b');
    $this->assertEqual($bill->id, '3caca1e0a68fa94d5bf073fdfc1ef9db2a1b');
    $this->assertEqual($bill->amount_cents, 1000);
    $this->assertEqual($bill->memo, 'test bill');
    $this->assertEqual($bill->identifier, '66c870a194837234235c299d');
    $this->assertEqual($bill->customer_id, '');
    $this->assertEqual($bill->currency, 'USD');
    $this->assertEqual($bill->status, 'open');
    $this->assertFalse($bill->recurring);
    $this->assertEqual($bill->rendered_content, 'test content');
    $this->assertEqual($bill->date, '2012-07-11');
    $this->assertIsA($bill->vendor, 'Zipmark_Vendor');
    $this->assertEqual($bill->vendor->id, '3e919a6e-d8c1-11e0-9e1f-e394e5601a36');
    $this->assertEqual($bill->vendor->name, 'Test Vendor');
    $this->assertEqual($bill->vendor->identifier, 'test_vendor');
  }

  function testBillGetFail() {
    $response = loadFixture('bills/get_fail.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/bills/3caca1e0a68fa94d5bf073fdfc1ef9db2a1c'));

    try {
      $bill = Zipmark_Bill::get('3caca1e0a68fa94d5bf073fdfc1ef9db2a1c', $client);
      $this->fail("Expected Zipmark_NotFoundError");
    }
    catch (Zipmark_NotFoundError $e) {
      $this->pass("Received Zipmark_NotFoundError");
    }

    $this->assertEqual($response->statusCode, 404);
  }

  function testBillCreate() {
    $response = loadFixture('bills/create.http');

    $bill = new Zipmark_Bill();
    $bill->identifier = 'testbill8';
    $bill->amount_cents = 12345;
    $bill->bill_template_id = '7eadd7be-60eb-4054-a172-107d394585e2';
    $bill->memo = 'Test Bill #8';
    $bill->date = '2012-09-10';
    $bill->content = '{"content":"foo"}';

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('POST', '/bills', $bill->toJson()));

    $bill->create($client);

    $this->assertIsA($bill, 'Zipmark_Bill');
    $this->assertEqual($bill->amount_cents, 12345);
    $this->assertEqual($bill->memo, 'Test Bill #8');
    $this->assertEqual($bill->identifier, 'testbill8');
    $this->assertEqual($bill->status, 'open');
    $this->assertEqual($bill->rendered_content, 'foo');
    $this->assertIsA($bill->vendor, 'Zipmark_Vendor');
    $this->assertEqual($bill->vendor->id, '3e919a6e-d8c1-11e0-9e1f-e394e5601a36');
    $this->assertEqual($bill->vendor->name, 'Test Vendor');
    $this->assertEqual($bill->vendor->identifier, 'test_vendor');
  }

  function testBillCreateFail() {
    $response = loadFixture('bills/create_fail.http');

    $bill = new Zipmark_Bill();
    $bill->amount_cents = 12345;
    $bill->memo = 'Test Bill Create Fail';
    
    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('POST', '/bills', $bill->toJson()));

    try {
      $bill->create($client);
      $this->fail("Expected Zipmark_ValidationError");
    }
    catch (Zipmark_ValidationError $e) {
      $this->assertEqual($e->getMessage(), "bill - identifier: can't be blank, bill_template_id: can't be blank, bill: is not open");
      $this->pass("Received Zipmark_ValidationError");
    }

    $this->assertEqual($response->statusCode, 422);
  }

  function testBillToJson() {
    $bill = new Zipmark_Bill();
    $bill->amount_cents = 12350;
    $bill->memo = "Testing toJson";

    $json = $bill->toJson();
    $this->assertEqual($json, '{"bill":{"amount_cents":12350,"memo":"Testing toJson"}}');
  }
}

?>
