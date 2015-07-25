<?php

namespace mindplay\props;

/**
 * This abstract class represents a named property object with a known owner.
 *
 * @see PropertySet
 */
abstract class Property implements NameAware, OwnerAware
{
    /**
     * @var string
     */
    protected $property_name;

    /**
     * @var object
     */
    protected $property_owner;

    /**
     * @return string column name
     */
    public function getPropertyName()
    {
        return $this->property_name;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setPropertyName($value)
    {
        $this->property_name = $value;
    }

    /**
     * @return object owner object
     */
    public function getPropertyOwner()
    {
        return $this->property_owner;
    }

    /**
     * @param object $value
     *
     * @return void
     */
    public function setPropertyOwner($value)
    {
        $this->property_owner = $value;
    }
}
