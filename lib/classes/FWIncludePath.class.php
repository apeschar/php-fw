<?php

/**
 * @package framework
 */

/**
 * Include path manager
 *
 * @package framework
 */
class FWIncludePath
{
  /**
   * Flush include path
   *
   * @return void
   */
  public static function clean()
  {
    set_include_path('.');
  }

  /**
   * Prepend directory to include path
   *
   * @param string $directory,...
   * @return void
   */
  public static function prepend($directory)
  {
    $path = explode(PATH_SEPARATOR, get_include_path());
    foreach(func_get_args() as $dir) array_unshift($path, $dir);
    array_unshift($path, '.');
    set_include_path(implode(PATH_SEPARATOR, array_unique($path)));
  }
}

