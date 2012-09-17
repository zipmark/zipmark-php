<?php

class Zipmark_CallbacksTest extends UnitTestCase {
  public function testCallbacksGet() {
    $response = loadFixture('callbacks/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/callbacks'));

    $callbacks = Zipmark_Callbacks::get(null, $client);

    $this->assertIsA($callbacks, 'Zipmark_Callbacks');
    $this->assertEqual($callbacks->getHref(), '/callbacks');
    $this->assertEqual($callbacks->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
