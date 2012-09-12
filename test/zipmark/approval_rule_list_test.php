<?php

class Zipmark_ApprovalRuleListTest extends UnitTestCase {
  public function testApprovalRuleListGet() {
    $response = loadFixture('approval_rules/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/approval_rules'));

    $approval_rules = Zipmark_ApprovalRuleList::get(null, $client);

    $this->assertIsA($approval_rules, 'Zipmark_ApprovalRuleList');
    $this->assertEqual($approval_rules->getHref(), '/approval_rules');
    $this->assertEqual($approval_rules->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
