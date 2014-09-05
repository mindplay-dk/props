<?php

namespace mindplay\props;

use ReflectionClass;
use RuntimeException;

use mindplay\filereflection\ReflectionFile;

/**
 * This class represents a set of named property objects.
 *
 * As such, all of the public properties of any class derived from this class
 * should be declared using @property-annotations, and all public properties
 * should implement one or both of NameAware and OwnerAware interfaces, which
 * will enable properties to have their name and owner/parent injected.
 *
 * @see NameAware
 * @see OwnerAware
 */
abstract class PropertySet extends Property
{
    /**
     * Regular expression used to parse @property-annotations
     */
    const PROPERTY_PATTERN = '/^\s*\*+\s*\@property(?:\-read|\-write|)\s+([\w\\\\]+(?:\\[\\]|))\s+\$(\w+)/im';

    /**
     * @var Property[] map where property-name => Property instance
     */
    protected $_props;

    /**
     * @var bool true, if the init() method has been called
     */
    protected $_initialized = false;

    /**
     * @return Property[] map where property-name => Property instance
     */
    public function getProperties()
    {
        $this->_init();

        return $this->_props;
    }

    /**
     * Initialize (if not already initialized)
     *
     * @return void
     */
    final protected function _init()
    {
        if ($this->_initialized) {
            return; // already initialized
        }

        $this->_props = $this->createProperties();

        $this->_initialized = true;
    }

    /**
     * Default initialization function - override as needed
     */
    protected function init()
    {
        $this->_init();
    }

    /**
     * Construct Property objects based on @property-annotations
     *
     * @return Property[] map where property-name => Property object
     *
     * @throws RuntimeException
     */
    protected function createProperties()
    {
        $class = new ReflectionClass(get_class($this));
        $file = new ReflectionFile($class->getFileName());

        if (! $class->isSubclassOf(__NAMESPACE__ . '\\PropertySet')) {
            throw new RuntimeException('class ' . get_class($this) . ' is not a descendant of PropertySet');
        }

        if (preg_match_all(self::PROPERTY_PATTERN, $class->getDocComment(), $matches) === 0) {
            throw new RuntimeException('class ' . get_class($this) . ' has no @property-annotations');
        }

        $props = array();

        for ($i = 0; $i < count($matches[0]); $i ++) {
            $name = $matches[2][$i];
            $type = substr($file->resolveName($matches[1][$i]), 1);

            $prop = new $type();

            if ($prop instanceof NameAware) {
                $prop->setPropertyName($name);
            }

            if ($prop instanceof OwnerAware) {
                $prop->setPropertyOwner($this);
            }

            $props[$name] = $prop;
        }

        return $props;
    }

    /**
     * @hidden
     */
    public function __get($name)
    {
        $this->_init();

        return $this->_props[$name];
    }

    /**
     * @hidden
     */
    public function __set($name, $value)
    {
        throw new RuntimeException('properties of ' . get_class($this) . ' are read-only');
    }
}
