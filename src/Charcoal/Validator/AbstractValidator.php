<?php

namespace Charcoal\Validator;

use \Charcoal\Validator\ValidatabaleInterface as ValidatabaleInterface;
use \Charcoal\Validator\ValidatorInterface as ValidatorInterface;
use \Charcoal\Validator\ValidatableInterface as ValidatableInterface;
use \Charcoal\Validator\ValidatorResult as ValidatorResult;

/**
* An abstract class that implements most of ValidatorInterface.
*
* The only abstract method in the class is `validate()`
*/
abstract class AbstractValidator implements ValidatorInterface
{
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';

    /**
    * @var ValidatableInterface
    */
    protected $_model;

    /**
    * @var array $_results array of ValidatorResult
    */
    private $_results;

    public function __construct(ValidatableInterface $model)
    {
        $this->_model = $model;
        $this->_results = [];
    }

    public function error($msg)
    {
        return $this->log(self::ERROR, $msg);
    }

    public function warning($msg)
    {
        return $this->log(self::WARNING, $msg);
    }

    public function notice($msg)
    {
        return $this->log(self::NOTICE, $msg);
    }

    public function log($level, $msg)
    {
        $this->add_result([
            'ident'=>'',
            'level'=>$level,
            'message'=>$msg
        ]);
        return $this;
    }

    /**
    * @param array|ValidatorResult $result
    * @throws \InvalidArgumentException if result is not an array or object
    * @return AbstractValidator Chainable
    */
    public function add_result($result)
    {
        if (is_array($result)) {
            $result = new ValidatorResult($result);
        } else if (!($result instanceof ValidatorResult)) {
            throw new \InvalidArgumentException('ValidatorResult must be an array or a ValidatorResult object');
        }
        $level = $result->level();
        if (!isset($this->_results[$level])) {
            $this->_results[$level] = [];
        }
        $this->results[$level][] = $result;
        return $this;
    }

    /**
    * @return array
    */
    public function results()
    {
        return $this->_results;
    }

    /**
    * @return array
    */
    public function error_results()
    {
        if (!isset($this_results[self::ERROR])) {
            return [];
        }
        return $this_results[self::ERROR];
    }

    /**
    * @return array
    */
    public function warning_results()
    {
        if (!isset($this->_results[self::WARNING])) {
            return [];
        }
        return $this->_results[self::WARNING];
    }

    /**
    * @return array
    */
    public function notice_results()
    {
        if (!isset($this->_results[self::NOTICE])) {
            return [];
        }
        return $this->_results[self::NOTICE];
    }

    public function merge(ValidatorInterface $v, $ident)
    {
        $results = $v->results();
        foreach ($results as $level => $res) {
            foreach ($res as $r) {
                $r->ident = $ident;
                $this->_results[$level][] = $r;
            }
        }
    }

    abstract public function validate();
}
