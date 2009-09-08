<?php

/**
 * @package framework
 */

/**
 * Autoloader
 *
 * @package framework
 */
class FWAutoloader
{
  /**
   * @var array
   */
  protected static $_try = array(
    '%s/%s.class.php',
    '%s/%s.php',
  );

  /**
   * Load specified class
   *
   * @param string $class
   * @return void|false
   */
  public static function load($class)
  {
    $include_path = array_unique(explode(PATH_SEPARATOR, get_include_path()));
    
    foreach($include_path as $dir)
    {
      foreach(self::$_try as $try)
      {
        $file = sprintf($try, $dir, $class);
        if(is_file($file))
        {
          require_once $file;
          return;
        }
      }
    }

    return false;
  }

  /**
   * Register autoloader
   *
   * @return void
   */
  public static function register()
  {
    spl_autoload_register(array(__CLASS__, 'load'));
  }
}

