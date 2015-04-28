<?php

namespace Charcoal\Source;

use \Charcoal\Core\AbstractFactory as AbstractFactory;

class SourceFactory extends AbstractFactory
{
    static public function types()
    {
        return array_merge(parent::types(), [
            'database'       => '\Charcoal\Source\DatabaseSource'
        ]);
    }
}
