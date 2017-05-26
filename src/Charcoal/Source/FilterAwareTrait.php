<?php
namespace Charcoal\Source;

use InvalidArgumentException;

use Charcoal\Source\Filter;
use Charcoal\Source\FilterInterface;

trait FilterAwareTrait
{
    /**
     * Array of `Filter` objects
     * @var array $filters
     */
    protected $filters = [];

    /**
     * @param array $filters The filters to set.
     * @return Collection Chainable
     */
    public function setFilters(array $filters)
    {
        $this->filters = [];
        foreach ($filters as $f) {
            $options = null;
            $val = null;
            if (is_array($f)) {
                if (isset($f['options'])) {
                    $options = $f['options'];
                }
            }
            $this->addFilter($f, $val, $options);
        }
        return $this;
    }

    /**
     * Add a collection filter to the loader.
     *
     * There are 3 different ways of adding a filter:
     * - as a `Filter` object, in which case it will be added directly.
     *   - `addFilter($obj);`
     * - as an array of options, which will be used to build the `Filter` object
     *   - `addFilter(['property' => 'foo', 'val' => 42, 'operator' => '<=']);`
     * - as 3 parameters: `property`, `val` and `options`
     *   - `addFilter('foo', 42, ['operator' => '<=']);`
     *
     * @param string|array|Filter $param   The filter property, or a Filter object / array.
     * @param mixed               $val     Optional: Only used if the first argument is a string.
     * @param array               $options Optional: Only used if the first argument is a string.
     * @throws InvalidArgumentException If property is not a string or empty.
     * @return self    (Chainable)
     */
    public function addFilter($param, $val = null, array $options = null)
    {
        if ($param instanceof FilterInterface) {
            $filter = $param;
        } elseif (is_array($param)) {
            $filter = $this->createFilter();
            $filter->setData($param);
        } elseif (is_string($param) && $val !== null) {
            $filter = $this->createFilter();
            $filter->setProperty($param);
            $filter->setVal($val);
        } else {
            throw new InvalidArgumentException(
                'Parameter must be an array or a property ident.'
            );
        }

        if (is_array($options)) {
            $filter->setData($options);
        }

        if ($this->hasModel()) {
            $property = $filter->property();
            if ($property) {
                $p = $this->model()->p($property);
                if ($p) {
                    if ($p->l10n()) {
                        $translator = TranslationConfig::instance();

                        $ident = sprintf('%1$s_%2$s', $property, $translator->currentLanguage());
                        $filter->setProperty($ident);
                    }

                    if ($p->multiple()) {
                        $filter->setOperator('FIND_IN_SET');
                    }
                }
            }
        }

        $this->filters[] = $filter;

        return $this;
    }


    /**
     * Allows the creation of a group of collection filters.
     *
     * @param array      $filters Array of `Filter` or array filters.
     * @param array|null $options FilterGroup options (operand, active, etc).
     */
    public function addFilterGroup(array $filters, array $options = null)
    {
        $group = $this->createFilterGroup();
        $group->setModel($this->model());
        $group->setData($options);
        $group->setFilters($filters);

        $this->filters[] = $group;
        return $this;
    }

    /**
     * @return array
     */
    public function filters()
    {
        return $this->filters;
    }

    abstract function createFilter();
    abstract function createFilterGroup();
}
