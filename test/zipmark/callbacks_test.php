<?php

class Zipmark_CallbacksTest extends UnitTestCase {
  public function testCallbacksGet() {
    $rootResponse = loadFixture('root_list.http');
    $response = loadFixture('callbacks/list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $response, array('http://example.org/callbacks', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $callbacks = $client->callbacks->getAll();

    $this->assertIsA($callbacks, 'Zipmark_Collection');
    $this->assertEqual($callbacks->getHref(), 'http://example.org/callbacks');
    $this->assertEqual($callbacks->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
