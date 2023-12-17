<?php

namespace Interop\Container;

/**
 * Represents a service definition.
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
