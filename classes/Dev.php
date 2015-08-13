<?php

/**
 * A simple developer class.
 * 
 * This contains som ehelper functions for debugging.
 */
class Dev
{
  private static $messages = array();
  
  /**
   * Shortcut for the setMessage() function.
   */
  public static function m($message) { self::setMessage($message); }
  
  /**
   * Debug a message (a message can be anything).
   */
  public static function setMessage($message) {
    if (IS_DEVELOPER) {
      if (!IS_LOCAL) {
        self::$messages[] = var_export($message, TRUE);
      } else {
        var_dump($message);
      }
    }
  }
  
  /**
   * Get all the messages logged.
   */
  public static function getMessages() {
    return self::$messages;
  }
  
  /**
   * Check if there's any logged messages.
   */
  public static function hasMessages() {
    return !empty(self::$messages);
  }
}