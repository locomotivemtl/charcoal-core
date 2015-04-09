<?php

namespace Charcoal\View;

use \Charcoal\View\ViewControllerInterface as ViewControllerInterface;

/**
* Model View\Controller
*/
abstract class AbstractViewController implements ViewControllerInterface
{
    /**
    * @var \Charcoal\Model\Model $context
    */
    protected $_context;

    /**
    *
    */
    public function __construct($context=null)
    {
        $this->_context = $context;
    }

    /**
    * The Model View\Controller is a decorator around the Model.
    *
    * Because of (Mustache) template engine limitation, this also check for methods
    * because `__call()` can not be used.
    *
    * @param string $name
    *
    * @return mixed
    * @see    https://github.com/bobthecow/mustache.php/wiki/Magic-Methods
    */
    public function __get($name)
    {

        $context = $this->_context();
        if($context === null) {
            return null;
        }

        if(is_object($context)) {
            // Try methods
            if(is_callable([$context, $name])) {
                return call_user_func([$context, $name]);
            }
            // Try Properties
            if(isset($context->{$name})) {
                return $context->{$name};
            }
        }
        else if(is_array($context)) {
            if(isset($context[$name])) {
                return $mode[$name];
            }
        }

        return null;
    }

    /**
    * The Model View\Controller is a decorator around the Model
    *
    * @param string $name
    * @param mixed  $arguments
    *
    * @return mixed
    */
    public function __call($name, $arguments)
    {
        $context = $this->_context();
        if($context === null) {
            return null;
        }

        if(is_object($context)) {
            if(is_callable([$context, $name])) {
                return call_user_func_array([$context, $name], $arguments);
            }
        }
        else if(is_array($context)) {
            if(isset($context[$name])) {
                return $context[$name];
            }
        }

        return null;
    }
    
    /**
    * @param string $name
    *
    * @return boolean
    */
    public function __isset($name)
    {
        $context = $this->_context();
        if($context === null) {
            return false;
        }

        if(is_object($name)) {
            // Try methods
            if(is_callable([$context, $name])) {
                return true;
            }

            // Try Properties
            if(isset($context->{$name})) {
                return true;
            }
        }
        else if(is_array($context)) {
            if(isset($context[$name])) {
                return $context[$name];
            }
        }
        return false;
    }

    public function set_context($context)
    {
        $this->_context = $context;
        return $this;
    }
    
    public function context()
    {
        return $this->_context;
    }
}
