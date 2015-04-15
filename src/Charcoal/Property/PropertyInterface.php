<?php

namespace Charcoal\Property;

/**
*
*/
interface PropertyInterface
{
    /**
    * @param array $data
    * @return PropertyInterface Chainable
    */
    public function set_data($data);

    /**
    * @param string $ident
    * @return PropertyInterface Chainable
    */
    public function set_ident($ident);

    /**
    * @return string
    */
    public function ident();

    /**
    * @param mixed
    * @return PropertyInterface Chainable
    */
    public function set_val($val);

    /**
    * @return mixed
    */
    public function val();

    /**
    * @param string $field_ident
    */
    public function field_val($field_ident);

    /**
    * @param mixed $label
    * @return PropertyInterface Chainable
    */
    public function set_label($label);

    /**
    * @return boolean
    */
    public function label();

    /**
    * @param boolean
    * @return PropertyInterface Chainable
    */
    public function set_l10n($l10n);

    /**
    * @return boolean
    */
    public function l10n();

    /**
    * @param boolean
    * @return PropertyInterface Chainable
    */
    public function set_hidden($hidden);

    /**
    * @return boolean
    */
    public function hidden();

    /**
    * @param boolean
    * @return PropertyInterface Chainable
    */
    public function set_multiple($multiple);

    /**
    * @return boolean
    */
    public function multiple();

    /**
    * @param array
    * @return PropertyInterface Chainable
    */
    public function set_multiple_options($multiple_options);

    /**
    * @return array
    */
    public function multiple_options();
    
    /**
    * @param boolean
    * @return PropertyInterface Chainable
    */
    public function set_required($required);

    /**
    * @return boolean
    */
    public function required();

    /**
    * @param boolean
    * @return PropertyInterface Chainable
    */
    public function set_unique($unique);

    /**
    * @return boolean
    */
    public function unique();

    /**
    * @param boolean
    * @return PropertyInterface Chainable
    */
    public function set_active($active);

    /**
    * @return boolean
    */
    public function active();

    /**
    * @return array
    */
    public function fields();

    /**
    * @return string
    */
    public function sql_type();
}
