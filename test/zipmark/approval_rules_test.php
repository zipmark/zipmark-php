<?php

class Zipmark_ApprovalRulesTest extends UnitTestCase
{
  public function testApprovalRulesGet()
  {
    $rootResponse = loadFixture('root_list.http');
    $response = loadFixture('approval_rules/list.http');

    $http = new MockZipmark_Http();
    $http->returns('GET', $rootResponse, array('/', null));
    $http->returns('GET', $response, array('http://example.org/approval_rules', null));

    $client = new Zipmark_Client(null, null, null, $http);

    $approval_rules = $client->approval_rules->getAll();

    $this->assertIsA($approval_rules, 'Zipmark_Collection');
    $this->assertEqual($approval_rules->getHref(), 'http://example.org/approval_rules');
    $this->assertEqual($approval_rules->count(), 2);
    $this->assertEqual($response->statusCode, 200);
  }
}

?>
