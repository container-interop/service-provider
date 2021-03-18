<?php

namespace Interop\Container;

/**
 * This interface defines the common facet of service factories and extensions.
 */
interface ServiceDefinitionInterface
{
    /**
     * Retrieves the keys of known, dependent service keys.
     *
     * @return string[] A list of strings each representing the key of a service.
     */
    public function getDependencies(): array;
}
