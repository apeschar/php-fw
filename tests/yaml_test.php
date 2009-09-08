<?php

require_once '_core.php';
require_once 'FWYAML.class.php';

class FWYAMLTest extends UnitTestCase
{
  public function testYAML()
  {
    $data_dir = dirname(__FILE__) . '/data/FWYAMLTest';
    $this->assertEqual(FWYAML::load($data_dir . '/test.yml'), array('key1' => 'a', 'key2' => 'b'));
    $this->assertEqual(file_get_contents($data_dir . '/test.yml'), FWYAML::dump(array('key1' => 'a', 'key2' => 'b')));
  }
}

