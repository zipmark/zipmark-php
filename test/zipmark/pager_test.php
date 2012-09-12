<?php

class Zipmark_PagerTest extends UnitTestCase {
  public function testPagerPointers() {
    $response = loadFixture('bills/list_p1_of_3.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/bills'));

    $bills = Zipmark_Bill::all(null, $client);

    $this->assertEqual($bills->key(), 0);
    $this->assertEqual($bills->count(), 8);
    $this->assertEqual($bills->page(), 1);
    $this->assertEqual($bills->numPages(), 3);
    $this->assertEqual($bills->perPage(), 3);
  }

  public function testNextPrev() {
    $response = loadFixture('bills/list_p1_of_3.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/bills'));

    $bills = Zipmark_Bill::all(null, $client);

    $currentBill = $bills->current();

    $this->assertIsA($currentBill, 'Zipmark_Bill');
    $this->assertEqual($bills->key(), 0);

    $nextBill = $bills->next();

    $this->assertIsA($nextBill, 'Zipmark_Bill');
    $this->assertEqual($bills->key(), 1);
    $this->assertNotEqual($currentBill, $nextBill);

    $prevBill = $bills->prev();

    $this->assertIsA($prevBill, 'Zipmark_Bill');
    $this->assertEqual($bills->key(), 0);
    $this->assertEqual($currentBill, $prevBill);
    $this->assertNotEqual($prevBill, $nextBill);
  }

  public function testNextPrevPageChange() {
    $responsePage1 = loadFixture('bills/list_p1_of_3.http');
    $responsePage2 = loadFixture('bills/list_p2_of_3.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $responsePage1, array('GET', '/bills'));
    $client->returns('request', $responsePage1, array('GET', 'http://example.org/bills?page=1'));
    $client->returns('request', $responsePage2, array('GET', 'http://example.org/bills?page=2'));

    $bills = Zipmark_Bill::all(null, $client);

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
    $responsePage1 = loadFixture('bills/list_p1_of_3.http');
    $responsePage2 = loadFixture('bills/list_p2_of_3.http');
    $responsePage3 = loadFixture('bills/list_p3_of_3.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $responsePage1, array('GET', '/bills'));
    $client->returns('request', $responsePage1, array('GET', 'http://example.org/bills?page=1'));
    $client->returns('request', $responsePage2, array('GET', 'http://example.org/bills?page=2'));
    $client->returns('request', $responsePage3, array('GET', 'http://example.org/bills?page=3'));

    $bills = Zipmark_Bill::all(null, $client);

    $this->assertEqual($bills->key(), 0);
    $this->assertEqual($bills->page(), 1);

    // Try to retrieve the item in position -1
    $prevBill = $bills->prev();
    $currentBill = $bills->current();

    $this->assertEqual($prevBill, null);
    $this->assertIsA($currentBill, 'Zipmark_Bill');
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
    $this->assertIsA($currentBill, 'Zipmark_Bill');
    $this->assertEqual($bills->key(), 7);
    $this->assertEqual($bills->page(), 3);
  }
}

?>
