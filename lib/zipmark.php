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
require_once('zipmark/approval_rule_list.php');
require_once('zipmark/bill.php');
require_once('zipmark/bill_list.php');
require_once('zipmark/callback.php');
require_once('zipmark/callback_list.php');
require_once('zipmark/disbursement.php');
require_once('zipmark/disbursement_list.php');
require_once('zipmark/vendor.php');
require_once('zipmark/vendor_relationship.php');
require_once('zipmark/vendor_relationship_list.php');

?>
