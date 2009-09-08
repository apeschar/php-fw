<?php

/**
 * @package framework
 */

/**
 * Class making using sessions easier
 *
 * @package framework
 */
class FWSessionNamespace
{
  /**
   * @var string
   */
  protected $namespace;

  /**
   * Constructor
   *
   * @param string $namespace
   */
  public function __construct($namespace)
  {
    $namespace = (string) $namespace;
    $this->namespace = $namespace;
  }

  /**
   * Get value
   *
   * @param string $name
   * @return mixed
   */
  public function &__get($name)
  {
    if(isset($_SESSION['FWSession'][$this->namespace][$name]))
    {
      return $_SESSION['FWSession'][$this->namespace][$name];
    }
    else
    {
      $null = null;
      return $null;
    }
  }

  /**
   * Set value
   *
   * @param string $name
   * @param mixed $value
   */
  public function __set($name, $value)
  {
    $_SESSION['FWSession'][$this->namespace][$name] = $value;
  }

  /**
   * Is value set?
   *
   * @param string $name
   * @return boolean
   */
  public function __isset($name)
  {
    return isset($_SESSION['FWSession'][$this->namespace][$name]);
  }
}

