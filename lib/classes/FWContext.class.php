<?php

/**
 * @package framework
 */

/**
 * Context class
 *
 * @package framework
 */
class FWContext
{
  /**
   * @var FWRouter
   */
  private static $_router;

  /**
   * Set router
   *
   * @param FWRouter $router
   * @return void
   */
  public static function setRouter(FWRouter $router)
  {
    self::$_router = $router;
  }

  /**
   * Get router
   *
   * @return FWRouter
   */
  public static function getRouter()
  {
    return self::$_router;
  }
}

