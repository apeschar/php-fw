<?php

/**
 * @package framework
 */

/**
 * Error handler class
 *
 * @package framework
 */
class FWErrorHandler
{
  /**
   * Data directory
   *
   * @var string
   */
  public static $_dataDirectory;

  /**
   * Debug mode
   *
   * @var boolean
   */
  protected static $_debug = false;

  /**
   * @var array
   */
  protected static $_relax = array();
 
  /**
   * Enable error handler
   *
   * @return void
   */
  public static function enable()
  {
    set_error_handler(array(__CLASS__, '_PHPErrorHandler'), E_ALL | E_STRICT);
    set_exception_handler(array(__CLASS__, '_ExceptionHandler'));
  }

  /**
   * Enable or disable debug mode
   *
   * @param boolean $enable
   * @return void
   */
  public static function setDebug($enable)
  {
    assert('is_bool($enable)');
    self::$_debug = $enable;
  }

  /**
   * Ignore certain errors
   *
   * @param integer $errno
   * @param string $dir
   * @param string $substr
   */
  public static function relax($errno, $dir = null, $substr = null)
  {
    assert('is_integer($errno)');
    assert('is_null($dir) || is_string($dir)');
    assert('is_null($substr) || is_string($substr)');

    // $dir exists?
    if(!is_dir($dir) && !is_file($dir))
      throw new Exception('Not a file nor a directory: ' . $dir);
    $dir = realpath($dir);
    if(!$dir)
      throw new Exception('Couldn\'t get realpath() of dir.');

    // add to list
    self::$_relax[] = array('errno' => $errno, 'dir' => $dir, 'substr' => $substr);
  }

  /**
   * Reset assert() options
   *
   * @return void
   */
  public static function resetAssertOptions()
  {
    assert_options(ASSERT_ACTIVE, 1);
    assert_options(ASSERT_WARNING, 1);
    assert_options(ASSERT_BAIL, 0);
    assert_options(ASSERT_QUIET_EVAL, 0);
    assert_options(ASSERT_CALLBACK, null);
  }

  /**
   * Handle PHP error
   *
   * @param integer $errno
   * @param string $errstr
   * @param string $errfile
   * @param string $errline
   * @param array $errcontext
   * @return void
   */
  public static function _PHPErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
  {
    $errrealfile = realpath($errfile);
    if($errrealfile) $errfile = $errrealfile;

    foreach(self::$_relax as $relax)
    {
      if(isset($relax['dir']) && !($relax['errno'] & $errno)) continue;
      if(isset($relax['dir']) && !($relax['dir'] == $errfile || strpos($errfile, $relax['dir']) === 0)) continue;
      if(isset($relax['substr']) && strpos($errstr, $relax['substr']) === false) continue;
      return;
    }

    throw new FWPHPException($errno, $errstr, $errfile, $errline, $errcontext);
  }

  /**
   * Handle exception
   *
   * @param Exception $exception
   * @return void
   */
  public static function _ExceptionHandler($exception)
  {
    if(!$exception instanceof Exception)
    {
      die(sprintf('Uncaught %s which is not an Exception.', htmlentities(get_class($exception), ENT_QUOTES, 'UTF-8')));
    }
    elseif($exception instanceof FWPHPException)
    {
      $errno = $exception->getCode();
      $errname = self::_getErrorName($errno);
      $errstr = $exception->getMessage();
      $errfile = $exception->getFile();
      $errline = $exception->getLine();
      $errcontext = $exception->context;

      require_once self::$_dataDirectory . (self::$_debug ? '/php_error_debug.php' : '/error.php');
    }
    else
    {
      require_once self::$_dataDirectory . (self::$_debug ? '/exception_debug.php' : '/error.php');
    }

    exit;
  }

  /**
   * Get error constant name when given value
   *
   * @param integer $value
   * @return string
   */
  protected static function _getErrorName($value)
  {
    static $error_constants;

    // get all E_* constants
    if(!isset($error_constants))
    {
      $error_constants = array();
      foreach(get_defined_constants() as $errname => $errvalue)
      {
        if(strpos($errname, 'E_') === 0)
        {
          $error_constants[$errname] = $errvalue;
        }
      }
    }

    // return error name
    return array_search($value, $error_constants);
  }
}

FWErrorHandler::$_dataDirectory = dirname(__FILE__) . '/../../data/ErrorHandler';

/**
 * PHP error exception
 *
 * @package framework
 */
class FWPHPException extends Exception
{
  public $context;

  /**
   * Constructor
   *
   * @param integer $errno
   * @param string $errstr
   * @param string $errfile
   * @param string $errline
   * @param array $errcontext
   * @return void
   */
  public function __construct($errno, $errstr, $errfile, $errline, $errcontext)
  {
    $this->context = $errcontext;

    parent::__construct($errstr, $errno);
    $this->file = $errfile;
    $this->line = $errline;
  }
}

