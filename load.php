<?php

// define constants if they aren't yet
if(!defined('FW_DIR')) define('FW_DIR', dirname(__FILE__));
if(!defined('DEVELOPMENT')) define('DEVELOPMENT', false);

// check constants
if(!defined('APP_DIR')) die('[fw] APP_DIR not defined.');

// start session
session_name('webapp');
session_start();

// output buffering
if(!DEVELOPMENT)
{
  if(function_exists('ob_gzhandler'))
  {
    while(ob_get_level() > 0) ob_end_clean();
    ob_start();
    ob_start('ob_gzhandler');
  }
  elseif(ob_get_level() == 0)
  {
    ob_start();
  }
}

// UTF8 encoding
header('Content-Type: text/html; charset=UTF-8');

// include path
require_once FW_DIR . '/lib/classes/FWIncludePath.class.php';
FWIncludePath::clean();
FWIncludePath::prepend(FW_DIR . '/lib/classes');
FWIncludePath::prepend(APP_DIR . '/lib');
FWIncludePath::prepend(APP_DIR . '/lib/forms');

require_once 'exceptions.php';

// error handler
require_once 'FWErrorHandler.class.php';
FWErrorHandler::resetAssertOptions();
FWErrorHandler::enable();
if(DEVELOPMENT) FWErrorHandler::setDebug(true);

// autoloader
require_once 'FWAutoloader.class.php';
FWAutoloader::register();

// i18n
if(defined('APP_LOCALE'))
  FWI18N::setLocale(APP_LOCALE);

// load propel
if(APP_ENABLE_PROPEL)
{
  FWIncludePath::prepend(FW_DIR . '/lib/vendor/propel/runtime/classes');
  FWIncludePath::prepend(APP_DIR . '/model/build/classes/propel');
  FWIncludePath::prepend(APP_DIR . '/model/build/classes');
  require 'propel/Propel.php';
  Propel::init(APP_DIR . '/model/build/conf/propel-conf.php');
}

