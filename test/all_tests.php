#!/usr/bin/php

<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../');

require_once 'simpletest/autorun.php';
require_once 'lib/zipmark.php';
require_once 'test_helpers.php';

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors','On');

Mock::generate('Zipmark_Http', 'MockZipmark_Http', array('GET', 'POST', 'PUT'));

class AllTests extends TestSuite {
  function AllTests() {
    $rootPath = dirname(__FILE__) . "/../";
    $this->TestSuite('All tests');
    $this->addFile($rootPath . "test/zipmark/client_test.php");
    $this->addFile($rootPath . "test/zipmark/collection_test.php");
    $this->addFile($rootPath . "test/zipmark/approval_rule_test.php");
    $this->addFile($rootPath . "test/zipmark/approval_rules_test.php");
    $this->addFile($rootPath . "test/zipmark/bill_test.php");
    $this->addFile($rootPath . "test/zipmark/bills_test.php");
    $this->addFile($rootPath . "test/zipmark/callback_test.php");
    $this->addFile($rootPath . "test/zipmark/callbacks_test.php");
    $this->addFile($rootPath . "test/zipmark/disbursement_test.php");
    $this->addFile($rootPath . "test/zipmark/disbursements_test.php");
    $this->addFile($rootPath . "test/zipmark/vendor_relationships_test.php");
  }
}

?>
