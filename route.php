<?php

// php version
if(version_compare(PHP_VERSION, '<', '5.2.3')) die('[fw] PHP >= 5.2.3 required.');

// check constants
if(!defined('FW_DIR')) die('[fw] FW_DIR not defined.');
if(!is_file(FW_DIR . '/route.php')) die('[fw] FW_DIR incorrect.');

// load framework
require FW_DIR . '/load.php';

// create router
$router = new FWRouter();
$router->setBaseURL('/');
$router->loadYAMLFile(APP_DIR . '/conf/routes.yml');
if(is_file(APP_DIR . '/conf/routes.conf')) $router->loadConfFile(APP_DIR . '/conf/routes.conf');
FWContext::setRouter($router);

// dispatch request
$request = new FWRequest();
$request->setRouter($router);
$request->dispatch(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '');

