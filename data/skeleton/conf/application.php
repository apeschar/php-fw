<?php

// are we in development mode?
define('DEVELOPMENT', true);

// application directory
define('APP_DIR', realpath(dirname(__FILE__) . '/..'));

// the directory in which the framework is installed
if(is_dir(APP_DIR . '/lib/vendor/framework'))
  define('FW_DIR', APP_DIR . '/lib/vendor/framework');
else
  define('FW_DIR', '//var/www/lib/framework/');

// other directories
define('FW_CACHE_DIR', APP_DIR . '/cache');
define('FW_TEMPLATE_DIR', APP_DIR . '/templates');

// enable propel
define('APP_ENABLE_PROPEL', false);

// i18n language
define('APP_LOCALE', 'en_US');

