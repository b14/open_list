<?php

/**
 * A simple event handler class.
 *
 *
 */
class EventHandler {
  /**
   * The array holding all the listener functions.
   *
   * It's build up the following way:
   * root[event_name][priority][add_order] = listener_function
   */
  private static $listeners = array();

  /**
   * A private constructor.
   */
  private function __construct() { }

  /**
   * Add a listener to an event.
   */
  public final static function addListener($name, $listener, $priority = 0) {
    if (is_callable($listener, FALSE, $function_name)) {
      if (!isset(self::$listeners[$name])) {
        self::$listeners[$name] = array();
      }
      self::$listeners[$name][$priority][$function_name] = $listener;
    }
  }

  /**
   * Trigger an event, with some arguments.
   */
  public final static function trigger($name, $args = array()) {
    if (!isset(self::$listeners[$name])) {
      return;
    }
    
    $call_list = array();
    foreach (self::$listeners[$name] as $priority_list) {
      foreach ($priority_list as $function_name => $listener) {
        $call_list[$function_name] = call_user_func_array($listener, $args);
      }
    }
    
    return $call_list;
  }
}