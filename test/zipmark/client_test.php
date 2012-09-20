<?php

class ZipmarkClientTest extends UnitTestCase
{
  function testRequest()
  {
    $rootResponse = loadFixture('root_list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $response = $client->request('GET', '/');

    $this->assertIsA($response, 'Zipmark_ClientResponse');
    $this->assertEqual($response->statusCode, 200);
  }

  function test__get()
  {
    $rootResponse = loadFixture('root_list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $approval_rules = $client->approval_rules;
    $bills = $client->bills;
    $callbacks = $client->callbacks;
    $invalid = $client->not_a_real_type;
    
    $this->assertIsA($approval_rules, 'Zipmark_Collection');
    $this->assertIsA($bills, 'Zipmark_Collection');
    $this->assertIsA($callbacks, 'Zipmark_Collection');
    $this->assertEqual($invalid, null);
  }

  function testAppId()
  {
    $client = new Zipmark_Client('myAppId');

    $appId = $client->appId();

    $this->assertEqual($appId, 'myAppId');
  }

  function testAppSecret()
  {
    $client = new Zipmark_Client(null, 'myAppSecret');

    $appSecret = $client->appSecret();

    $this->assertEqual($appSecret, 'myAppSecret');
  }

  function testApiUrl()
  {
    $client = new Zipmark_Client();

    $sandboxApiUrl = $client->apiUrl();

    $this->assertEqual($sandboxApiUrl, 'https://sandbox.zipmark.com');

    $client->setProduction(true);

    $productionApiUrl = $client->apiUrl();

    $this->assertEqual($productionApiUrl, 'https://api.zipmark.com');
  }

  function testAllResources()
  {
    $rootResponse = loadFixture('root_list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $allResources = $client->allResources();

    $this->assertEqual(
      $allResources,
      array(
        'approval_rules',
        'bills',
        'callbacks',
        'disbursements',
        'vendor_relationships'
      )
    );
  }

  function test_loadFromFail()
  {
    $rootResponse = loadFixture('root_list_error.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));

    $client = new Zipmark_Client(null, null, null, $http);

    try {
      $allResources = $client->allResources();
      $this->fail("Expected Zipmark_Error");
    } catch (Zipmark_Error $e) {
      $this->assertEqual($e->getMessage(), "Root response does not contain vendor_root");
      $this->pass("Received Zipmark_Error");
    }

  }
}

?>
