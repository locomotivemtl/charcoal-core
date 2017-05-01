<?php

namespace Charcoal\Source;

interface FilterGroupInterface
{
    /**
     * @param array $data The filter data.
     * @return Filter Chainable
     */
    public function setData(array $data);

    /**
     * @param boolean $active The active flag.
     * @return FilterInterface Chainable
     */
    public function setActive($active);

    /**
     * @return boolean
     */
    public function active();

    /**
     * @param string $operand The filter operand.
     * @return FilterInterface Chainable
     */
    public function setOperand($operand);
    /**
     * @return string
     */
    public function operand();
}
