<?php

namespace Charcoal\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\FilterGroupInterface;
use Charcoal\Source\FilterAwareTrait;
use Charcoal\Source\ModelAwareTrait;

use Charcoal\Source\AbstractSource;

/**
 * FilterGroup
 */
class FilterGroup implements FilterGroupInterface
{
    use FilterAwareTrait;
    use ModelAwareTrait;

    /**
     * Default operand when none given.
     * @var string
     */
    const DEFAULT_OPERAND  = 'AND';

    /**
     * @var string $operand
     */
    protected $operand = self::DEFAULT_OPERAND;

    /**
     * Inactive filter should be skipped completely.
     * @var boolean $active
     */
    protected $active;


    /**
     * @param array $data The filter data.
     * @return Filter Chainable
     */
    public function setData(array $data = null)
    {
        if (!$data) {
            return $this;
        }
        if (isset($data['operand'])) {
            $this->setOperand($data['operand']);
        }

        if (isset($data['active'])) {
            $this->setActive($data['active']);
        }

        return $this;
    }

    /**
     * @param string $operand The filter operand.
     * @throws InvalidArgumentException If the parameter is not a valid operand.
     * @return Filter (Chainable)
     */
    public function setOperand($operand)
    {
        if (!is_string($operand)) {
            throw new InvalidArgumentException(
                'Operand should be a string.'
            );
        }

        $operand = strtoupper($operand);
        if (!in_array($operand, $this->validOperands())) {
            throw new InvalidArgumentException(
                'This is not a valid operand.'
            );
        }

        $this->operand = $operand;
        return $this;
    }

    /**
     * @return string
     */
    public function operand()
    {
        return strtoupper($this->operand);
    }

    /**
     * @param boolean $active The active flag.
     * @return Filter (Chainable)
     */
    public function setActive($active)
    {
        $this->active = !!$active;
        return $this;
    }

    /**
     * @return boolean
     */
    public function active()
    {
        return $this->active;
    }

    /**
     * Supported operand types, uppercase
     *
     * @return array
     */
    protected function validOperands()
    {
        $validOperands = [
            'AND', '&&',
            'OR', '||',
            'XOR'
        ];

        return $validOperands;
    }

    public function createFilterGroup()
    {
        return new FilterGroup();
    }

    public function createFilter()
    {
        return new Filter();
    }

}
