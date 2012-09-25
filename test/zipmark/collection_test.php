<?php

class Zipmark_CollectionTest extends UnitTestCase
{
  public function testCollectionRetrieve()
  {
    $response = loadFixture('root_list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $response, array('/', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills;

    $this->assertIsA($bills, 'Zipmark_Collection');
  }

  public function testCollectionGetAll()
  {
    $rootResponse = loadFixture('root_list.http');
    $response = loadFixture('bills/list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $response, array('http://example.org/bills', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills->getAll();

    $this->assertIsA($bills, 'Zipmark_Collection');
    $this->assertEqual($bills->getHref(), 'http://example.org/bills');
    $this->assertEqual($bills->count(), 22);
    $this->assertEqual($response->statusCode, 200);
  }

  public function testCollectionPointers()
  {
    $rootResponse = loadFixture('root_list.http');
    $billResponse = loadFixture('bills/list_p1_of_3.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $billResponse, array('http://example.org/bills', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills->getAll();

    $this->assertEqual($bills->count(), 8);
    $this->assertEqual($bills->page(), 1);
    $this->assertEqual($bills->numPages(), 3);
    $this->assertEqual($bills->perPage(), 3);
  }

  public function testGetResource()
  {
    $rootResponse = loadFixture('root_list.http');
    $billResponse = loadFixture('bills/list_p1_of_3.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $billResponse, array('http://example.org/bills', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills->getAll();

    $bill = $bills->getResource(2);

    $this->assertIsA($bill, 'Zipmark_Resource');
    $this->assertEqual($bill->id, "3cea079b288120ffb129dfb62ae18de3dfee");    
  }

  public function testLoadLink()
  {
    $rootResponse = loadFixture('root_list.http');
    $responsePage1 = loadFixture('bills/list_p1_of_3.http');
    $responsePage2 = loadFixture('bills/list_p2_of_3.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $responsePage1, array('http://example.org/bills', null));
    $http->returns('GET', $responsePage2, array('http://example.org/bills?page=2', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills->getAll();

    $this->assertEqual($bills->page(), 1);

    $bills->loadLink('next');

    $this->assertEqual($bills->page(), 2);
  }

  public function testLoadPage()
  {
    $rootResponse = loadFixture('root_list.http');
    $responsePage1 = loadFixture('bills/list_p1_of_3.http');
    $responsePage3 = loadFixture('bills/list_p3_of_3.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $responsePage1, array('http://example.org/bills', null));
    $http->returns('GET', $responsePage3, array('http://example.org/bills?page=3', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills->getAll();

    $this->assertEqual($bills->page(), 1);

    $bills->loadPage(3);

    $this->assertEqual($bills->page(), 3); 
  }
}

?>
