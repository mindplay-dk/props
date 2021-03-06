<?php

namespace mindplay\props;

/**
 * Property-types used in a PropertySet will be made aware of their
 * owner/parent object, if this interface is implemented.
 *
 * @see PropertySet::createProperties()
 */
interface OwnerAware
{
    /**
     * @return object
     */
    function getPropertyOwner();

    /**
     * @param object $value
     *
     * @return void
     */
    function setPropertyOwner($value);
}
