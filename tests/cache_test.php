<?php

require_once '_core.php';
require_once 'FWCache.class.php';

class FWCacheTest extends UnitTestCase
{
  protected function _testBasic($cache)
  {
    // test basic features
    $this->assertEqual($cache->set('key', 'value'), 'value');
    $this->assertEqual($cache->get('key'), 'value');
    $this->assertEqual($cache->remove('key'), 'value');
    $this->assertEqual($cache->get('key'), null);
    $this->assertEqual($cache->get('key', 'default'), 'default');
  }

  protected function _testFlush($cache)
  {
    // test flushing
    $this->assertEqual($cache->set('key', 'value'), 'value');
    $this->assertEqual($cache->get('key'), 'value');
    $cache->flush();
    $this->assertEqual($cache->get('key'), null);
  }

  protected function _testDriver($driver)
  {
    $cache = new FWCache($driver);
    $this->_testBasic($cache);
    $this->_testFlush($cache);
  }

  public function testCache()
  {
    $this->_testDriver('Array');
  }
}

