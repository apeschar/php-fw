<?php

require_once '_core.php';

class AllTests extends TestSuite
{
  public function AllTests()
  {
    $this->TestSuite('All tests');
    $this->addFile('router_test.php');
    $this->addFile('u_test.php');
    $this->addFile('cache_test.php');
    $this->addFile('yaml_test.php');
  }
}

