<?php

class ZipmarkCallbackTest extends UnitTestCase {
  function testCallbackGet() {
    $response = loadFixture('callbacks/get.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $response, array('/callbacks/85172b58-f3e5-46d9-ba61-3c0cf769caa0', null));

    $client = new Zipmark_Client(null, null, false, null, $http);

    $callback = $client->callbacks->get('85172b58-f3e5-46d9-ba61-3c0cf769caa0');

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

    $callback_data = array(
      'event' => 'bill.update',
      'url'   => 'https://example.com/callbacks',
    );

    $callback_json = json_encode(array("callback" => $callback_data));

    $http = new MockZipmark_Http();
    $http->returns('POST', $response, array('/callbacks', $callback_json));

    $client = new Zipmark_Client(null, null, false, null, $http);

    $callback = $client->callbacks->create($callback_data);

    $this->assertIsA($callback, 'Zipmark_Callback');
    $this->assertEqual($callback->api_version, 'v2');
    $this->assertEqual($callback->event, 'bill.update');
    $this->assertEqual($callback->status, 'active');
    $this->assertEqual($callback->url, 'https://example.com/callbacks');
  }

  function testCallbackCreateFail() {
    $response = loadFixture('callbacks/create_fail.http');

    $callback_data = array(
      'event' => 'bill.create',
      'url'   => 'https://example.com/callbacks',
    );

    $callback_json = json_encode(array("callback" => $callback_data));
    
    $http = new MockZipmark_Http();
    $http->returns('POST', $response, array('/callbacks', $callback_json));

    $client = new Zipmark_Client(null, null, false, null, $http);

    try {
      $client->callbacks->create($callback_data);
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
