<?php

class ZipmarkApprovalRuleTest extends UnitTestCase {
  function testApprovalRuleAll() {
    $response = loadFixture('approval_rules/list.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/approval_rules'));

    $approval_rules = Zipmark_ApprovalRule::all(null, $client);

    $this->assertIsA($approval_rules, 'Zipmark_ApprovalRules');
    $this->assertEqual($approval_rules->getHref(), '/approval_rules');
    $this->assertEqual($approval_rules->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
  
  function testApprovalRuleGet() {
    $response = loadFixture('approval_rules/get.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/approval_rules/9671336a-ee0f-4f98-8e84-b8d221a2b3f3'));

    $approval_rule = Zipmark_ApprovalRule::get('9671336a-ee0f-4f98-8e84-b8d221a2b3f3', $client);

    $this->assertIsA($approval_rule, 'Zipmark_ApprovalRule');
    $this->assertEqual($approval_rule->getHref(), 'http://example.org/approval_rules/9671336a-ee0f-4f98-8e84-b8d221a2b3f3');
    $this->assertEqual($approval_rule->id, '9671336a-ee0f-4f98-8e84-b8d221a2b3f3');
    $this->assertEqual($approval_rule->period, 'Monthly');
    $this->assertEqual($approval_rule->amount_cents, 10000);
  }

  function testApprovalRuleGetFail() {
    $response = loadFixture('approval_rules/get_fail.http');

    $client = new MockZipmark_Client();
    $client->returns('request', $response, array('GET', '/approval_rules/9671336a-ee0f-4f98-8e84-b8d221a2b3f3'));

    try {
      $approval_rule = Zipmark_ApprovalRule::get('9671336a-ee0f-4f98-8e84-b8d221a2b3f3', $client);
      $this->fail("Expected Zipmark_NotFoundError");
    }
    catch (Zipmark_NotFoundError $e) {
      $this->pass("Received Zipmark_NotFoundError");
    }

    $this->assertEqual($response->statusCode, 404);
  }

  function testApprovalRulePath() {
    $approval_rule = new Zipmark_ApprovalRule();
    $path = $approval_rule->pathFor("rule123");

    $this->assertEqual($path, "/approval_rules/rule123");
  }
}

?>
