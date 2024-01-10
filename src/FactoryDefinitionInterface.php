<?php

namespace Interop\Container;

use Psr\Container\ContainerInterface;

/**
 * A factory optionally implements this interface to reflect it's dependencies.
 */
interface FactoryDefinitionInterface
{
    /**
     * Creates the entry, using the given container to resolve dependencies.
     * 
     * @param ContainerInterface $container The container that should be used to resolve dependencies
     *
     * @return mixed The created entry
     */
    public function __invoke(ContainerInterface $container): mixed;
}
