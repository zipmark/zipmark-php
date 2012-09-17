<?php

date_default_timezone_set('UTC');

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../');

require_once('zipmark/base.php');
require_once('zipmark/client.php');
require_once('zipmark/client_response.php');
require_once('zipmark/errors.php');
require_once('zipmark/pager.php');
require_once('zipmark/resource.php');

require_once('zipmark/approval_rule.php');
require_once('zipmark/approval_rules.php');
require_once('zipmark/bill.php');
require_once('zipmark/bills.php');
require_once('zipmark/callback.php');
require_once('zipmark/callbacks.php');
require_once('zipmark/disbursement.php');
require_once('zipmark/disbursements.php');
require_once('zipmark/vendor.php');
require_once('zipmark/vendor_relationship.php');
require_once('zipmark/vendor_relationships.php');

?>
