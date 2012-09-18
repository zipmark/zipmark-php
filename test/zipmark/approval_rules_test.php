<?php

class Zipmark_ApprovalRulesTest extends UnitTestCase {
  public function testApprovalRulesGet() {
    $response = loadFixture('approval_rules/list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $response, array('/approval_rules', null));

    $client = new Zipmark_Client(null, null, false, null, $http);

    $approval_rules = $client->approval_rules->get();

    $this->assertIsA($approval_rules, 'Zipmark_ApprovalRules');
    $this->assertEqual($approval_rules->getHref(), '/approval_rules');
    $this->assertEqual($approval_rules->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }

  function testApprovalRulesPath() {
    $approval_rules = new Zipmark_ApprovalRules();
    $path = $approval_rules->pathFor();

    $this->assertEqual($path, "/approval_rules");
  }
}

?>
