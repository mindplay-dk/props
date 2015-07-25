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
