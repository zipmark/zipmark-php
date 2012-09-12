<?php

class Zipmark_CallbackListTest extends UnitTestCase {
  public function testCallbackListGet() {
    $response = loadFixture('callbacks/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/callbacks'));

    $callbacks = Zipmark_CallbackList::get(null, $client);

    $this->assertIsA($callbacks, 'Zipmark_CallbackList');
    $this->assertEqual($callbacks->getHref(), '/callbacks');
    $this->assertEqual($callbacks->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
