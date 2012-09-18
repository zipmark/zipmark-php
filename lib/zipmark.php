<?php

date_default_timezone_set('UTC');

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../');

require_once('zipmark/base.php');
require_once('zipmark/client.php');
require_once('zipmark/client_response.php');
require_once('zipmark/errors.php');
require_once('zipmark/pager.php');
require_once('zipmark/resource.php');
require_once('zipmark/editable_resource.php');

require_once('zipmark/object_classes.php');

?>
