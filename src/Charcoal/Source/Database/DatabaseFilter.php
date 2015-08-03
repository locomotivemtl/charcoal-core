<?php

namespace Charcoal\Source\Database;

// Local parent namespace dependencies
use \Charcoal\Source\Filter as Filter;

/**
*
*/
class DatabaseFilter extends Filter
{
    /**
    * @return string
    */
    public function sql()
    {
        if ($this->_string) {
            return $this->_string;
        }
        $fields = $this->sql_fields();
        if (empty($fields)) {
            return '';
        }

        $filter = '';
        foreach ($fields as $field) {
            $val = '\''.$this->val().'\'';

            // Support custom "operator" for the filter
            $operator = $this->operator();

            // Support for custom function on column name
            $function = $this->func();

            if ($function) {
                $target = $function.'(`'.$field.'`)';
            } else {
                $target = '`'.$field.'`';
            }

            switch ($operator) {
                /*
                case '=':

                if($this->multiple() && ($sql_val != "''")) {
                $sep = (isset($this->multiple_options['separator']) ? $this->multiple_options['separator'] : ',');
                if($sep == ',') {
                $filter = ' FIND_IN_SET('.$sql_val.', '.$filter_ident.')';
                }
                else {
                // The FIND_IN_SET function must work on a comma separated-value.
                // So create temporary separators to use a comma...
                $custom_separator = '}x5S_'; // With not much luck, this string should never be used in text
                $filter = ' FIND_IN_SET(
                REPLACE('.$sql_val.', \',\', \''.$custom_separator.'\'),
                REPLACE(REPLACE('.$filter_ident.', \',\', \''.$custom_separator.'\'), \''.$sep.'\', \',\')';
                }
                }
                else {
                $filter = '('.$filter_ident.' '.$operator.' '.$sql_val.')';
                }
                break;
                */

                case 'IS NULL':
                case 'IS NOT NULL':
                    $filter .= '('.$target.' '.$operator.')';
                    break;

                default:
                    $filter .= '('.$target.' '.$operator.' '.$val.')';
                    break;
            }
        }

        return $filter;
    }

    /**
    * @return array
    */
    private function sql_fields()
    {
        $property = $this->property();
        if ($property) {
            /** @todo Load Property from associated model metadata. */
            return [$property];
        }
        /*
        $field = $this->field();
        if($field) {
        return [$field];
        }
        */
        return [];
    }
}