<?php

class ZipmarkCallbackTest extends UnitTestCase {
  function testCallbackAll() {
    $response = loadFixture('callbacks/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/callbacks'));

    $callbacks = Zipmark_Callback::all(null, $client);

    $this->assertIsA($callbacks, 'Zipmark_CallbackList');
    $this->assertEqual($callbacks->getHref(), '/callbacks');
    $this->assertEqual($callbacks->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }

  function testCallbackGet() {
    $response = loadFixture('callbacks/get.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/callbacks/85172b58-f3e5-46d9-ba61-3c0cf769caa0'));

    $callback = Zipmark_Callback::get('85172b58-f3e5-46d9-ba61-3c0cf769caa0', $client);

    $this->assertIsA($callback, 'Zipmark_Callback');
    $this->assertEqual($callback->getHref(), 'http://example.org/callbacks/85172b58-f3e5-46d9-ba61-3c0cf769caa0');
    $this->assertEqual($callback->id, '85172b58-f3e5-46d9-ba61-3c0cf769caa0');
    $this->assertEqual($callback->api_version, "v2");
    $this->assertEqual($callback->event, 'bill.create');
    $this->assertEqual($callback->status, 'active');
    $this->assertEqual($callback->url, 'https://example.com/callbacks');
  }

  function testCallbackCreate() {
    $response = loadFixture('callbacks/create.http');

    $callback = new Zipmark_Callback();
    $callback->event = 'bill.update';
    $callback->url = 'https://example.com/callbacks';
    
    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('POST', '/callbacks', $callback->toJson()));

    $callback->create($client);

    $this->assertIsA($callback, 'Zipmark_Callback');
    $this->assertEqual($callback->api_version, 'v2');
    $this->assertEqual($callback->event, 'bill.update');
    $this->assertEqual($callback->status, 'active');
    $this->assertEqual($callback->url, 'https://example.com/callbacks');
  }

  function testCallbackCreateFail() {
    $response = loadFixture('callbacks/create_fail.http');

    $callback = new Zipmark_Callback();
    $callback->event = 'bill.create';
    $callback->url = 'http://example.com/callbacks';
    
    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('POST', '/callbacks', $callback->toJson()));

    try {
      $callback->create($client);
      $this->fail("Expected Zipmark_ValidationError");
    }
    catch (Zipmark_ValidationError $e) {
      $this->assertEqual($e->getMessage(), "callback - url: is invalid.  You must use https://");
      $this->pass("Received Zipmark_ValidationError");
    }

    $this->assertEqual($response->statusCode, 422);
  }

  function testCallbackToJson() {
    $callback = new Zipmark_Callback();
    $callback->event = 'bill.create';
    $callback->url = 'https://example.com/callbacks';

    $json = $callback->toJson();
    $this->assertEqual($json, '{"callback":{"event":"bill.create","url":"https:\/\/example.com\/callbacks"}}');
  }
}

?>
