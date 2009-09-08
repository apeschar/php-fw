<?php

/**
 * @package framework
 */

/**
 * Base class for cache backends
 *
 * @package framework
 */
abstract class FWCacheBackendAbstract
{
  /**
   * Get value
   *
   * @param string $key
   * @return string|null
   */
  abstract public function get($key);

  /**
   * Set value
   *
   * @param string $key
   * @param string $value
   * @param null|integer $lifetime
   * @return void
   */
  abstract public function set($key, $value, $lifetime = null);

  /**
   * Remove value
   *
   * @param string $key
   * @return string|null
   */
  abstract public function remove($key);

  /**
   * Remove all values
   *
   * @return void
   */
  public function flush()
  {
  }
}

