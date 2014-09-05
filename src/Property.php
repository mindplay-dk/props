<?php

namespace mindplay\props;

/**
 * This abstract class represents a named property object with a known owner.
 *
 * @see PropertySet
 */
class Property implements NameAware, OwnerAware
{
    /**
     * @var string
     */
    protected $_name;

    /**
     * @var object
     */
    protected $_owner;

    /**
     * Empty constructor
     */
    public function __construct()
    {}

    /**
     * @return string column name
     */
    public function getPropertyName()
    {
        return $this->_name;
    }

    /**
     * @param string $value
     *
     * @return void
     */
    function setPropertyName($value)
    {
        $this->_name = $value;
    }

    /**
     * @return object owner object
     */
    public function getPropertyOwner()
    {
        return $this->_owner;
    }

    /**
     * @param object $value
     *
     * @return void
     */
    function setPropertyOwner($value)
    {
        $this->_owner = $value;
    }
}
