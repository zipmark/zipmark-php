<?php

class ZipmarkCallbackTest extends UnitTestCase
{
  function testCallbackFields()
  {
    $client = new Zipmark_Client(
      'OWNjY2FiMmQtOWVhNy00OTVmLTg5YmEtOGQyMThjNWU1ZTg5',
      '101e9484bf96d848e6e18a70b03cd894996d3b2f5ccf3775bfc8ff2c4b5ebf7858a10203ad816184d841b137b8de188825a77d35bb5a6a63b679453715b7e791'
    );

    $headerFile = loadFile('callback/headers.json');
    $bodyFile = loadFile('callback/body.json');

    // Headers should be an array
    $headers = json_decode($headerFile[0], true);

    // Body should be a JSON string
    $body = $bodyFile[0];

    $callback = new Zipmark_Callback($client, $headers, $body);

    $this->assertTrue($callback->isValid());
    $this->assertEqual($callback->event(), 'bill.create');
    $this->assertEqual($callback->objectType(), 'bill');
    $this->assertIsA($callback->object(), 'Zipmark_Resource');
  }

  function testCallbackFieldsInvalidResponse()
  {
    $client = new Zipmark_Client('wrong_identifier', 'wrong_secret');
    
    $headerFile = loadFile('callback/headers_invalid.json');
    $bodyFile = loadFile('callback/body.json');

    // Headers should be an array
    $headers = json_decode($headerFile[0], true);

    // Body should be a JSON string
    $body = $bodyFile[0];

    $callback = new Zipmark_Callback($client, $headers, $body);

    $this->assertFalse($callback->isValid());
    $this->assertNull($callback->event());
    $this->assertNull($callback->objectType());
    $this->assertNull($callback->object());
  }
}

?>
