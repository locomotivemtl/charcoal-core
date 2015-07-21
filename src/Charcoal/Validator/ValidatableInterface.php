<?php

namespace Charcoal\Validator;

// Local namespace dependencies
use \Charcoal\Validator\ValidatorInterface as ValidatorInterface;

/**
*
*/
interface ValidatableInterface
{
    /**
    * @param ValidatorInterface $validator
    * @return ValidatableInterface Chainable
    */
    public function set_validator(ValidatorInterface $validator);

    /**
    * @return ValidatorInterface
    */
    public function validator();

    /**
    * @param ValidatorInterface $v
    * @return boolean
    */
    public function validate(ValidatorInterface &$v = null);
}
