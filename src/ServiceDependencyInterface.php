<?php

namespace Interop\Container;

/**
 * A `ServiceProviderInterface` implementation may optionally implements this interface,
 * which provides a means of reflecting the dependencies of the provided services.
 */
interface ServiceDependencyInterface
{
    /**
     * @return array<string,string[]> map where entry ID => list of dependency IDs
     */
    public function getDependencies(): array;
}
