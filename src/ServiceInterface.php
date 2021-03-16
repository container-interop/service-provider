<?php

namespace Interop\Container;

/**
 * A factory optionally implements this interface to reflect it's dependencies.
 */
interface ServiceInterface
{
    /**
     * Retrieves the keys of known, dependent services.
     *
     * @return string[] A list of strings each representing the key of a service.
     */
    public function getDependencies(): array;
}
