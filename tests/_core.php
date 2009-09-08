<?php

error_reporting(E_ALL);

require_once 'simpletest/autorun.php';

set_include_path(
  '.'
  . PATH_SEPARATOR . dirname(__FILE__)
  . PATH_SEPARATOR . dirname(__FILE__) . '/../lib/classes'
  . PATH_SEPARATOR . get_include_path()
);

