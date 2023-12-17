<?php

namespace Interop\Container;

/**
 * This interface defines the common facet of service factories and extensions.
 */
interface ServiceDefinitionInterface
{
    /**
     * Retrieves the keys services that are known dependencies of this service..
     *
     * @return string[] A list of strings each representing the key of a service.
     */
    public function getDependencies(): array;
}
