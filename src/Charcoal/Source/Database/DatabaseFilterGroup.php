<?php

namespace Charcoal\Source\Database;

use \InvalidArgumentException;

// From 'charcoal-core'
use \Charcoal\Source\FilterGroup;

/**
 * DatabaseFilterGroup
 */
class DatabaseFilterGroup extends FilterGroup
{
    /**
     * [sql description]
     * @return [type] [description]
     */
    public function sql()
    {
        $filters = $this->filters();
        $sql = ' (';
        foreach ($filters as $i => $f) {
            if ($i > 0) {
                $sql .= ' '. $f->operand();
            }
            $sql .= ' ' . $f->sql();
        }

        $sql = rtrim($sql);
        $sql .= ' ) ';

        return $sql;
    }

    public function createFilterGroup()
    {
        return new DatabaseFilterGroup();
    }

    public function createFilter()
    {
        return new DatabaseFilter();
    }
}
