<?php

require_once '_core.php';
require_once 'FWU.class.php';

class FWUTest extends UnitTestCase
{
  public function testUnderscoresCamelcase()
  {
    $uc = array(
      'simple_test'             => 'SimpleTest',
    );

    foreach($uc as $u => $c)
    {
      $this->assertEqual(FWU::underscoresToCamelcase($u), $c);
    }
  }

  public function testRandomString()
  {
    $re = '|^[A-Za-z][A-Za-z0-9]*$|';

    $this->assertTrue(preg_match($re, $s = FWU::randomString()));
    $this->assertEqual(strlen($s), 40);
    
    $this->assertTrue(preg_match($re, $s = FWU::randomString(1)));
    $this->assertEqual(strlen($s), 1);
  }
}

