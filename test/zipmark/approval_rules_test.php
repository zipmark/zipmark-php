<?php

class Zipmark_ApprovalRulesTest extends UnitTestCase {
  public function testApprovalRulesGet() {
    $response = loadFixture('approval_rules/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/approval_rules'));

    $approval_rules = Zipmark_ApprovalRules::get(null, $client);

    $this->assertIsA($approval_rules, 'Zipmark_ApprovalRules');
    $this->assertEqual($approval_rules->getHref(), '/approval_rules');
    $this->assertEqual($approval_rules->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
