<?php

namespace Charcoal\Metadata;

// Intra-module (`charcoal-core`) dependencies
use \Charcoal\Property\PropertyInterface as PropertyInterface;

// Local namespace dependencies
use \Charcoal\Metadata\MetadataInterface as MetadataInterface;

/**
*
*/
interface DescribableInterface
{
    /**
    * @param array $data
    * @return DescribableInterface Chainable
    */
    public function set_data(array $data);

    /**
    * @param array|MetadataInterface $metadata
    * @return DescribableInterface Chainable
    */
    public function set_metadata($metadata);

    /**
    * @return MetadataInterface
    */
    public function metadata();

    /**
    * @param string $metadata_ident
    * @return MetadataInterface
    */
    public function load_metadata($metadata_ident = null);

    /**
    * @param string $metadata_ident
    * @return DescribableInterface Chainable
    */
    public function set_metadata_ident($metadata_ident);

    /**
    * @return string
    */
    public function metadata_ident();

    /**
    * Get the list of properties, as array of `PropertyInterface`
    *
    * @return array
    */
    public function properties();

    /**
    * Get a single property
    *
    * @param string $property_ident
    * @return PropertyInterface
    */
    public function property($property_ident);

    /**
    * Alias of `property()` or `properties()`, depending if argument is set or not.
    *
    * @param mixed $property_ident Property ident, if null, return all properties
    * @return array|PropertyInterface|null
    */
    public function p($property_ident = null);
}
