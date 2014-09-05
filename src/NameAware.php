<?php

namespace mindplay\props;

/**
 * Property-types used in a PropertySet will be made aware of their
 * own names, if this interface is implemented.
 *
 * @see PropertySet::createProperties()
 */
interface NameAware
{
    /**
     * Properties must implement an empty constructor
     */
    function __construct();

    /**
     * @return string
     */
    function getPropertyName();

    /**
     * @param string $value
     *
     * @return void
     */
    function setPropertyName($value);
}
