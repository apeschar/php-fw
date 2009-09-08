<?php

/**
 * @package framework
 */

/**
 * Base class
 *
 */
require_once dirname(__FILE__) . '/FWCacheBackendAbstract.class.php';

/**
 * Dummy cache backend
 *
 * @package framework
 */
class FWCacheBackendArray extends FWCacheBackendAbstract
{
  private $_cache = array();

  /**
   * Set value
   *
   * @param string $key
   * @param string $value
   * @param null|integer $lifetime
   * @return void
   */
  public function set($key, $value, $lifetime = null)
  {
    $this->_cache[$key] = array($value, $lifetime ? time() + $lifetime : null);
  }

  /**
   * Get value
   *
   * @param string $key
   * @return string|null
   */
  public function get($key)
  {
    if(isset($this->_cache[$key]))
    {
      if($this->_cache[$key][1] && time() > $this->_cache[$key][1])
      {
        unset($this->_cache[$key]);
        return;
      }
      return $this->_cache[$key][0];
    }
  }

  /**
   * Remove value
   *
   * @param string $key
   * @return string|null
   */
  public function remove($key)
  {
    $return = isset($this->_cache[$key]) ? $this->_cache[$key][0] : null;
    unset($this->_cache[$key]);
    return $return;
  }

  /**
   * Remove all values
   *
   * @return void
   */
  public function flush()
  {
    $this->_cache = array();
  }
}

