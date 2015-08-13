<?php

/**
 * This class sends the calls through to the the OpenList.
 *
 * It's a simple automated decorator pattern
 *
 * @see http://en.wikipedia.org/wiki/Decorator_pattern
 */
class OpenListDecorator
{
  public $open_list = null;

  /**
   *
   */
  public function __construct() {
    $this->open_list = new OpenList();
  }

  /**
   * 
   */
  public function __call($method, $args) {
    if (method_exists($this->open_list, $method)) {
      // Trigger an event before the call.
      // This event passes the arguments as a reference, so the modules can
      // change the input.
      EventHandler::trigger('pre_method_call', array($method, &$args));
      
      // Call the OpenList function.
      $result = call_user_func_array(array($this->open_list, $method), $args);

      // Triggers an event after the call.
      // Here the result is passed by reference, so the modules can do last
      // second changes to it.
      EventHandler::trigger('post_method_call', array($method, &$result));
      
      // Insert any debugging information available into the result.
      if (Dev::hasMessages()) {
        $result = array('result' => $result);
        $result['DEBUG'] = Dev::getMessages();
      }

      return $result;
    }
    // If the OpenList object didn't have the method, we trigger an event for
    // the modules, and we throw the bad method call exception:
    // http://php.net/manual/en/class.badmethodcallexception.php
    else {
      EventHandler::trigger('bad_method_call', array($method, $args));
      throw new BadMethodCallException();
    }
  }
}