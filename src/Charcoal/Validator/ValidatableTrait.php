<?php

namespace Charcoal\Validator;

// Local namespace dependencies
use \Charcoal\Validator\ValidatorInterface as ValidatorInterface;

/**
* A full default implementation, as trait, of the ValidatableInterface.
*
* There is one additional abstract method: `create_validator()`
*/
trait ValidatableTrait
{
    /**
    * In-objet copy of the `ValidatorInterface` validator object
    * @var ValidatorInterface $_validator
    */
    protected $_validator;

    /**
    * @param ValidatorInterface $validator
    * @return ValidatableInterface Chainable
    */
    public function set_validator(ValidatorInterface $validator)
    {
        $this->_validator = $validator;
        return $this;
    }

    /**
    * @return ValidatorInterface
    */
    public function validator()
    {
        if ($this->_validator === null) {
            $this->_validator = $this->create_validator();
        }
        return $this->_validator;
    }

    /**
    * @return ValidatorInterface
    */
    abstract protected function create_validator();

    /**
    * @param ValidatorInterface $v
    * @return boolean
    */
    public function validate(ValidatorInterface &$v = null)
    {
        if ($v !== null) {
            $this->set_validator($v);
        }

        return $this->validator()->validate();
    }
}
