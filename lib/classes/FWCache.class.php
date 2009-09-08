<?php

/**
 * @package framework
 */

/**
 * Caching class
 *
 * @package framework
 */
class FWCache
{
  /**
   * Instance
   *
   * @var FWCache
   */
  private static $_instance;

  /**
   * Backend
   *
   * @var FWCacheBackendAbstract
   */
  protected $_backend;

  /**
   * Prefix
   *
   * @var string
   */
  protected $_prefix = '';

  /**
   * Timestamp of flush
   *
   * @var integer
   */
  protected $_flushedAt;

  /**
   * Constructor
   *
   * @param string $backend_name
   * @param array $frontend_options
   * @param array $backend_options
   */
  public function __construct($backend_name = 'Array', $frontend_options = array(), $backend_options = array())
  {
    assert('is_string($backend_name)');
    assert('is_array($frontend_options)');
    assert('is_array($backend_options)');

    // create backend instance
    $backend_class = 'FWCacheBackend' . $backend_name;
    if(!class_exists($backend_class))
    {
      $backend_file = dirname(__FILE__) . '/' . $backend_class . '.class.php';
      if(is_file($backend_file)) require_once $backend_file;
    }
    if(!class_exists($backend_class)) throw new Exception('Backend does not exist: ' . $backend_name);
    $this->_backend = new $backend_class($backend_options);

    // frontend options
    foreach($frontend_options as $option => $value)
    {
      if($option == 'prefix')
      {
        assert('is_string($value)');
        $this->_prefix = $value;
      }
      else
      {
        throw new Exception('Unknown option: ' . $option);
      }
    }
  }

  /**
   * Get instance
   *
   * @return FWCache
   */
  public static function getInstance()
  {
    if(isset(self::$_instance)) return self::$_instance;
    self::$_instance = new self('Array');
    return self::$_instance;
  }

  /**
   * Set instance
   *
   * @param FWCache $instance
   * @return void
   */
  public static function setInstance(FWCache $instance)
  {
    self::$_instance = $instance;
  }

  /**
   * Set value
   *
   * @param string $key
   * @param mixed $value
   * @param integer $lifetime
   * @return mixed
   */
  public function set($key, $value, $lifetime = null)
  {
    assert('is_string($key)');
    assert('is_null($lifetime) || is_integer($lifetime)');
    $string_value = $lifetime ? time() + $lifetime : '';
    $string_value .= ',' . time() . ',' . serialize($value);
    $full_key = $this->_prefix . $key;
    $this->_backend->set($key, $string_value, $lifetime);
    return $value;
  }

  /**
   * Get value
   *
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public function get($key, $default = null)
  {
    assert('is_string($key)');
    $value = $this->_backend->get($key);
    if($value === null) return $default;
    return $this->_processValue($value, $default);
  }

  /**
   * Remove value and return old value
   *
   * @param string $key
   * @return mixed
   */
  public function remove($key)
  {
    assert('is_string($key)');
    $value = $this->_backend->remove($key);
    if($value === null) return null;
    return $this->_processValue($value);
  }

  /**
   * Process value
   *
   * @param string $value
   * @param mixed $default
   * @return mixed
   */
  protected function _processValue($value, $default = null)
  {
    assert('is_string($value)');
    list($expire, $created, $value) = explode(',', $value, 3);
    if($this->_flushedAt && $created <= $this->_flushedAt) return $default;
    if($expire && time() > $expire) return $default;
    return unserialize($value);
  }

  /**
   * Flush all existing items at the server
   *
   * @return void
   */
  public function flush()
  {
    $this->_flushedAt = time();
    $this->_backend->flush();
  }
}

