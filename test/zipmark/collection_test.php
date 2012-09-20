<?php

class Zipmark_CollectionTest extends UnitTestCase {
  public function testCollectionRetrieve() {
    $response = loadFixture('root_list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $response, array('/', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills;

    $this->assertIsA($bills, 'Zipmark_Collection');
  }

  public function testCollectionPointers() {
    $rootResponse = loadFixture('root_list.http');
    $billResponse = loadFixture('bills/list_p1_of_3.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $billResponse, array('http://example.org/bills', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills->getAll();

    $this->assertEqual($bills->key(), 0);
    $this->assertEqual($bills->count(), 8);
    $this->assertEqual($bills->page(), 1);
    $this->assertEqual($bills->numPages(), 3);
    $this->assertEqual($bills->perPage(), 3);
  }

  public function testNextPrev() {
    $rootResponse = loadFixture('root_list.http');
    $response = loadFixture('bills/list_p1_of_3.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $response, array('http://example.org/bills', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills->getAll();

    $currentBill = $bills->current();

    $this->assertIsA($currentBill, 'Zipmark_Resource');
    $this->assertEqual($bills->key(), 0);

    $nextBill = $bills->next();

    $this->assertIsA($nextBill, 'Zipmark_Resource');
    $this->assertEqual($bills->key(), 1);
    $this->assertNotEqual($currentBill, $nextBill);

    $prevBill = $bills->prev();

    $this->assertIsA($prevBill, 'Zipmark_Resource');
    $this->assertEqual($bills->key(), 0);
    $this->assertEqual($currentBill, $prevBill);
    $this->assertNotEqual($prevBill, $nextBill);
  }

  public function testNextPrevPageChange() {
    $rootResponse = loadFixture('root_list.http');
    $responsePage1 = loadFixture('bills/list_p1_of_3.http');
    $responsePage2 = loadFixture('bills/list_p2_of_3.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $responsePage1, array('http://example.org/bills', null));
    $http->returns('GET', $responsePage1, array('http://example.org/bills?page=1', null));
    $http->returns('GET', $responsePage2, array('http://example.org/bills?page=2', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills->getAll();

    $this->assertEqual($bills->page(), 1);
    $this->assertEqual($bills->perPage(), 3);
    // Advance to the first item on the next page
    $bills->next();
    $bills->next();
    $bills->next();
    $this->assertEqual($bills->page(), 2);
    // Move back to the last item on the previous page
    $bills->prev();
    $this->assertEqual($bills->page(), 1);
  }

  public function testNextPrevEndOfList() {
    $rootResponse = loadFixture('root_list.http');
    $responsePage1 = loadFixture('bills/list_p1_of_3.http');
    $responsePage2 = loadFixture('bills/list_p2_of_3.http');
    $responsePage3 = loadFixture('bills/list_p3_of_3.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $responsePage1, array('http://example.org/bills', null));
    $http->returns('GET', $responsePage1, array('http://example.org/bills?page=1', null));
    $http->returns('GET', $responsePage2, array('http://example.org/bills?page=2', null));
    $http->returns('GET', $responsePage3, array('http://example.org/bills?page=3', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $bills = $client->bills->getAll();

    $this->assertEqual($bills->key(), 0);
    $this->assertEqual($bills->page(), 1);

    // Try to retrieve the item in position -1
    $prevBill = $bills->prev();
    $currentBill = $bills->current();

    $this->assertEqual($prevBill, null);
    $this->assertIsA($currentBill, 'Zipmark_Resource');
    $this->assertEqual($bills->key(), 0);
    $this->assertEqual($bills->page(), 1);

    $bills->next();
    $bills->next();
    $bills->next();
    $bills->next();
    $bills->next();
    $bills->next();
    $bills->next();

    $this->assertEqual($bills->key(), 7);
    $this->assertEqual($bills->page(), 3);

    // Try to retrieve the item in max + 1
    $nextBill = $bills->next();
    $currentBill = $bills->current();

    $this->assertEqual($nextBill, null);
    $this->assertIsA($currentBill, 'Zipmark_Resource');
    $this->assertEqual($bills->key(), 7);
    $this->assertEqual($bills->page(), 3);
  }
}

?>
