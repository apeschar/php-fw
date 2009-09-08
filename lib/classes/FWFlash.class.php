<?php

/**
 * @package framework
 */

/**
 * Simple flash class
 *
 * @package framework
 */
class FWFlash
{
  /** 
   * Add message
   *
   * @param string $message
   */
  public static function addMessage($message)
  {
    self::getSession()->messages[sizeof(self::getSession()->messages)] = $message;
  }

  /**
   * Get messages
   *
   * @return array
   */
  public static function getMessages()
  {
    $messages = self::getSession()->messages;
    self::getSession()->messages = array();
    return $messages;
  }

  /**
   * Any messages?
   *
   */
  public static function hasMessages()
  {
    return !empty(self::getSession()->messages);
  }

  /**
   * Get session namespace
   *
   * @return FWSessionNamespace
   */
  protected static function getSession()
  {
    static $ns;
    if(!$ns) $ns = new FWSessionNamespace(__CLASS__);
    if(!$ns->messages) $ns->messages = array();
    return $ns;
  }
}

