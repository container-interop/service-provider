<?php

namespace Interop\Container;

use Psr\Container\ContainerInterface;

/**
 * An extension optionally implements this interface to reflect it's dependencies.
 */
interface ExtensionDefinitionInterface
{
    /**
     * Extends a given service, using the given container to resolve dependencies.
     * 
     * @param ContainerInterface $container The container that should be used to resolve dependencies
     * @param mixed              $previous  The previous service
     *
     * @return mixed The extended service.
     */
    public function __invoke(ContainerInterface $container, mixed $previous): mixed;
}
