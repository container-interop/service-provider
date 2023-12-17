<?php

namespace Interop\Container;

/**
 * Represents a service definition.
 */
interface ServiceDefinitionInterface
{
    /**
     * Retrieves the keys of known dependencies of this service.
     *
     * @return string[] A list of service keys.
     */
    public function getDependencies(): array;
}
