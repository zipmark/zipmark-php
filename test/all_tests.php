#!/usr/bin/php

<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../');

require_once("simpletest/autorun.php");
require_once("lib/zipmark.php");
require_once('test_helpers.php');

error_reporting(E_ALL);
ini_set('display_errors','On');

// Set default timezone
// date_default_timezone_set('UTC');

Mock::generate('Zipmark_Client');

class AllTests extends TestSuite {
  function AllTests() {
    $rootPath = dirname(__FILE__) . "/../";
    $this->TestSuite('All tests');
    $this->addFile($rootPath . "test/zipmark/approval_rule_list_test.php");
    $this->addFile($rootPath . "test/zipmark/approval_rule_test.php");
    $this->addFile($rootPath . "test/zipmark/bill_test.php");
    $this->addFile($rootPath . "test/zipmark/bill_list_test.php");
    $this->addFile($rootPath . "test/zipmark/callback_test.php");
    $this->addFile($rootPath . "test/zipmark/callback_list_test.php");
    $this->addFile($rootPath . "test/zipmark/disbursement_test.php");
    $this->addFile($rootPath . "test/zipmark/disbursement_list_test.php");
    $this->addFile($rootPath . "test/zipmark/vendor_relationship_test.php");
    $this->addFile($rootPath . "test/zipmark/vendor_relationship_list_test.php");
  }
}

?>
