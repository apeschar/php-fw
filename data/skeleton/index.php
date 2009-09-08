<?php

// php version
if(version_compare(PHP_VERSION, '<', '5.2.3')) die('PHP >= 5.2.3 required.');

// constants
require dirname(__FILE__) . '/conf/application.php';

// load framework
require FW_DIR . '/route.php';

