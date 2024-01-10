<?php

namespace Interop\Container;

use Psr\Container\ContainerInterface;

/**
 * A service provider provides entries to a container.
 */
interface ServiceProviderInterface
{
    /**
     * Returns a list of all container entries registered by this service provider.
     *
     * - the key is the entry name
     * - the value is a callable that will return the entry, aka the **factory**
     *
     * A factory is an instance of {@see FactoryDefinitionInterface}, or a `callable` with the following signature:
     * 
     *     function(\Psr\Container\ContainerInterface $container)
     *
     * @return array<string,((callable(ContainerInterface):mixed)|FactoryDefinitionInterface)>
     */
    public function getFactories(): array;

    /**
     * Returns a list of all container entries extended by this service provider.
     *
     * - the key is the entry name
     * - the value is a callable that will return the modified entry
     *
     * An extension is an instance of {@see ExtensionDefinitionInterface}, or a `callable` with the following signature:
     * 
     *        function(Psr\Container\ContainerInterface $container, $previous)
     *     or function(Psr\Container\ContainerInterface $container, $previous = null)
     *
     * About factories parameters:
     *
     * - the container (instance of `Psr\Container\ContainerInterface`)
     * - the entry to be extended. If the entry to be extended does not exist and the parameter is nullable, `null` will be passed.
     *
     * @return array<string,((callable(ContainerInterface,mixed):mixed)|ExtensionDefinitionInterface)[]>
     */
    public function getExtensions(): array;
}
