<?php

class Zipmark_CallbacksTest extends UnitTestCase {
  public function testCallbacksGet() {
    $response = loadFixture('callbacks/list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $response, array('/callbacks', null));

    $client = new Zipmark_Client(null, null, false, null, $http);

    $callbacks = $client->callbacks->get();

    $this->assertIsA($callbacks, 'Zipmark_Callbacks');
    $this->assertEqual($callbacks->getHref(), '/callbacks');
    $this->assertEqual($callbacks->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
