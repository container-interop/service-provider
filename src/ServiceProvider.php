<?php

namespace Interop\Container;

interface ServiceProvider
{
    /**
     * @return array
     */
    public static function getServices();
}
