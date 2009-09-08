<?php

/**
 * @package framework
 */

/**
 * I18N class
 *
 * @package framework
 */
class FWI18N
{
  /**
   * @var string
   */
  protected static $_locale = 'en_US';

  /**
   * @var string
   */
  protected static $_fallbackLocale = 'en_US';

  /**
   * @var array
   */
  protected static $_messages = array();

  /**
   * @var array
   */
  protected static $_directories = array();

  /**
   * Add directory
   *
   * @param string $dir
   */
  public static function addDirectory($dir)
  {
    assert('is_string($dir)');
    if(!is_dir($dir)) throw new Exception('Directory not found: ' . $dir);
    array_unshift(self::$_directories, $dir);
  }

  /**
   * Set locale
   *
   * @param string $locale
   */
  public static function setLocale($locale)
  {
    if(!self::_isValidLocale($locale)) throw new Exception('Invalid locale: ' . $locale);
    self::$_locale = $locale;
    if(!isset(self::$_messages[$locale])) self::$_messages[$locale] = array();
  }

  /**
   * Get locale
   *
   * @return string
   */
  public static function getLocale()
  {
    return self::$_locale;
  }

  /**
   * Get message
   *
   * @param string $component
   * @param string $key
   * @param string|null $default
   * @param string|null $locale
   * @return string|null
   */
  public static function get($component, $key, $default = null, $locale = null)
  {
    assert('is_string($component)');
    assert('is_string($key)');
    assert('is_null($default) || is_string($default)');
    assert('is_null($locale) || is_string($locale)');

    // what locale are we using?
    if(null === $locale)
    {
      $locale = self::getLocale();
    }
    else
    {
      if(!self::_isValidLocale($locale)) throw new Exception('Invalid locale: ' . $locale);
    }

    // component loaded?
    if(!isset(self::$_messages[$locale][$component]))
    {
      $component_file = $locale . '/' . $component . '.php';
      $found = false;
      foreach(self::$_directories as $dir)
      {
        $fullpath = "$dir/$component_file";
        if(is_file($fullpath))
        {
          require $fullpath;
          if(!isset($messages)) continue;
          self::$_messages[$locale][$component] = $messages;
          unset($messages);
          $found = true;
          break;
        }
      }

      if(!$found)
        return self::_getFallback($component, $key, $default, $locale);
    }

    // key available?
    if(!isset(self::$_messages[$locale][$component][$key]))
      return self::_getFallback($component, $key, $default, $locale);

    // key found!
    return self::$_messages[$locale][$component][$key];
  }

  /**
   * Validate a locale
   *
   * @param string $locale
   * @return boolean
   */
  protected static function _isValidLocale($locale)
  {
    if(!is_string($locale)) return false;
    return preg_match('|^[a-z]{2}_[A-Z]{2}$|', $locale);
  }

  /**
   * Get fallback value
   *
   * @param string $component
   * @param string $key
   * @param string|null $default
   * @param string $locale
   */
  protected static function _getFallback($component, $key, $default, $locale)
  {
    assert('is_string($component)');
    assert('is_string($key)');
    assert('is_null($default) || is_string($default)');

    if(self::$_fallbackLocale == $locale) return $default;
    return self::get($component, $key, $default, self::$_fallbackLocale);
  }
}

FWI18N::addDirectory(dirname(__FILE__) . '/../../i18n');

