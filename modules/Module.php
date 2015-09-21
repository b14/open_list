<?php

/**
 * @file
 * Bas emodule
 */

/**
 * The base class for a module.
 *
 * We extends the EventHandler so we can use protected functions for all
 * methods connect to an event.
 */
abstract class Module extends EventHandler {
  /**
   * An array holding all the modules.
   */
  private static $modules = array();

  /**
   * A simple manually set version of the module.
   */
  public $version = 0;

  /**
   * A module needs to hook into some events to function.
   *
   * This function should return an associative array, where the key is the
   * event name, and the value is the function to call.
   */
  abstract public function getEvents();

  /**
   * Initialize the module.
   *
   * Notice this is a final function, so any new module will not have a
   * constructor available.
   */
  public final function __construct() {
    self::$modules[get_class($this)] = $this;

    $this->setupEvents();
  }

  /**
   * Get the events from the getEvents() function.
   */
  private function setupEvents() {
    $events = $this->getEvents();
    foreach ($events as $event => $listener) {
      EventHandler::addListener($event, array($this, $listener));
    }
  }

  /**
   * Return a module object depending on the module name.
   *
   * @param string $module_name
   *   The name of the module.
   *
   * @return mixed
   *   The module object if the module exists. Otherwise false.
   */
  public final static function getModule($module_name) {
    if (isset(self::$modules[$module_name])) {
      return self::$modules[$module_name];
    }

    return FALSE;
  }

  /**
   * Call an admin method of the module.
   *
   * An admin method is required to start with an _ (underscore), so they
   * easily can be identified, but as an added bonus you wont be able to call
   * every method using this admin function.
   *
   * @param string $module_name
   *   The exact module name, to call the method on.
   * @param string $method
   *   The method to call (without the underscore).
   *
   * @return mixed
   *   The data returned from the admin function.
   */
  public final static function admin($module_name, $method) {
    if (isset(self::$modules[$module_name])) {
      $module = self::$modules[$module_name];
      $method = '_' . $method;

      if (method_exists($module, $method)) {
        return call_user_func_array(array($module, $method), array());
      }
    }

    return FALSE;
  }
}
