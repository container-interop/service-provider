# Standard service providers

This project tries to find a solution for cross-framework modules (aka bundles) through **standard container configuration**. It is part of the [container-interop](https://github.com/container-interop/container-interop) group.

**Work in progress:** the project is currently experimental and is being tried in frameworks, containers and modules until considered viable. Until a 1.0.0 release the code in this repository is not stable. Expect changes breaking backward compatibility between minor versions (0.1.x -> 0.2.x).

[![Gitter chat](https://badges.gitter.im/container-interop/definition-interop.png)](https://gitter.im/container-interop/definition-interop)

## Background

Three main alternatives were identified to standardize container configuration:

- standard PHP objects/interfaces representing container definitions
- standard container configuration format (e.g. XML, …)
- standard service providers

The first solution that container-interop members tried to implement was [a set of standard PHP interfaces for container definitions](https://github.com/container-interop/definition-interop). While this solution is working, it has a few limitations and it is complicated to explain, understand and use.

There were then discussions about a standard configuration format (for example in XML), which has the advantage of being slightly easier to understand and use for module developers. This work has not be formalized yet because of the amount of work needed. This approach would also suffers from a few of the limitations identified in the first approach. It would also requires the inclusion in the standard of many specific features: the standard must define many different ways for how objects can be created and dependencies injected. That makes the standard complex to define, and would force all containers (even simple ones) to support all the features.

This repository contains a proposition for **standard service providers** (service providers are PHP components that provide container entries). This approach has turned out to be simpler on many level:

- the standard is much simpler, which means it is easier to explain and understand
- it is easier to use as it relies on plain old PHP code
- it is easier to implement support in containers

## Usage

To declare a service provider, simply implement the `ServiceProviderInterface` interface.

```php
use Interop\Container\ServiceProviderInterface;

class MyServiceProvider implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            'my_service' => function(ContainerInterface $container) {
                $dependency = $container->get('my_other_service');
                return new MyService($dependency);
            }
        ];
    }
    
    public function getExtensions()
    {
        return [
                'my_extended_service' => function(ContainerInterface $container, $extendedService) {
                    $extendedService->registerExtension($container->get('my_service'));
                    return $extendedService;
                }
            ];
    }
}
```

### Factories

The `getFactories()` method must return a list of all container entries the service provider wishes to register:

- the key is the entry name
- the value is a callable that will return the entry, aka the **factory**

Factories have the following signature:

```php
function(ContainerInterface $container) : mixed
```

Factories accept one parameter:

- the container (instance of `Psr\Container\ContainerInterface`)

Each factory is responsible for returning a given entry of the container. Nothing should be cached by service providers, this is the responsibility of the container.

### Extensions

The `getExtensions()` method must return a list of all container entries the service provider wishes to extend:

- the key is the entry name
- the value is a callable that will return the entry, aka the **extension**

Extensions have the following signature:

```php
function(ContainerInterface $container, $previous [= null]) : mixed
```

Extensions accept the following parameters:

- the container (instance of `Psr\Container\ContainerInterface`)
- the service to be extended. This parameter CAN be typehinted on the expected service type and CAN be nullable.

### More about parameters

If you know you will not be using the `$container` parameter or the `$previous` parameter, you can omit them:

```php
    function() {
        return new MyService();
    }
```

### Consuming service providers

Service providers are typically consumed by containers.

For containers implementing *PSR-11*:

- A call to `get` on an entry defined in a service-provider MUST always return the same value.
- The container MUST cache the result returned by the factory and return the cached entry.

### Values (aka parameters)

A factory/extension can provide PHP objects (services) as well as any value. Simply return the value you wish from factory methods.

### Returning `null`

A factory/extension can return `null`. In this case, the container consuming the service provider MUST register a service that is `null`.

```php
    public function getFactories()
    {
        return [
            'my_service' => function() { return null; }
        ];
    }
```

```php
$container->has('my_service') // Returns true
$container->get('my_service') // Returns null
```

### Aliases

To alias a container entry to another, you can get the aliased entry from the container and return it:

```php
class MyServiceProvider implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            'my_service' => [ MyServiceProvider::class, 'createMyService' ],
            'alias' => [ MyServiceProvider::class, 'resolveAlias' ],
        ];
    }

    public function getExtensions()
    {
        return [];
    }

    // ...

    public static function resolveAlias(ContainerInterface $container)
    {
        return $container->get('my_service');
    }
}
```

### Entry overriding

Overriding an entry defined in another service provider is as easy as defining it again.

Module A:

```php
class A implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            'foo' => [ A::class,  'getFoo' ],
        ];
    }

    public function getExtensions()
    {
        return [];
    }

    public static function getFoo()
    {
        return 'abc';
    }
}
```

Module B:

```php
class B implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            'foo' => [ B::class, 'getFoo' ],
        ];
    }

    public function getExtensions()
    {
        return [];
    }

    public static function getFoo()
    {
        return 'def';
    }
}
```

If you register the service providers in the correct order in your container (A first, then B), then the entry `foo` will be `'def'` because B's definition will override A's.

### Entry extension

Extending an entry before it is returned by the container is done via the `getExtensions` method.

Module A:

```php
class A implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            'logger' => [ A::class, 'getLogger' ],
        ];
    }
    
    // ...

    public static function getLogger()
    {
        return new Logger;
    }
}
```

Module B:

```php
class B implements ServiceProviderInterface
{
    // ...
    
    public function getExtensions()
    {
        return [
            'logger' => [ B::class, 'getLogger' ],
        ];
    }

    public static function getLogger(ContainerInterface $container, $logger)
    {
        // Register a new log handler
        $logger->addHandler(new SyslogHandler());

        // Return the object that we modified
        return $logger;
    }
}
```

The second parameter of extensions SHOULD use type-hinting when applicable.

```php
public static function getLogger(ContainerInterface $container, Logger $logger)
```

If a container passes a service that does not match the type hint, a `TypeError` will be thrown while bootstrapping the Container (in PHP 7+), or a catchable fatal error in PHP 5.

The second parameter of extensions CAN be nullable.

```php
public static function getLogger(ContainerInterface $container, Logger $logger = null)
public static function getLogger(ContainerInterface $container, ?Logger $logger)
```

If an extension is defined for a service that does not exist, null will be passed as a second argument.

```php
class B implements ServiceProviderInterface
{
    // ...
    public function getExtensions()
    {
        return [
            'logger' => [ B::class, 'getLogger' ],
        ];
    }

    public static function getLogger(ContainerInterface $container, ?Logger $logger)
    {
        // If no logger service is defined, let's simply ignore this extension (instead of throwing an error)
        if ($logger === null) {
            return null;
        }
        
        // Register a new log handler
        $logger->addHandler(new SyslogHandler());

        // Return the object that we modified
        return $logger;
    }
}
```

### Consuming service providers

Containers consuming service providers MUST consume them in 2 passes.

1. In the first pass, the container calls the `getFactories` method of all service providers.
2. In the second pass, the container calls the `getExtensions` method of all service providers.

As a side-effect, even if service provider B is declared *before* service provider A, it can still extend services declared in service provider A.

## Compatible projects
### Projects consuming *service providers*

- [Laravel service provider bridge](https://github.com/thecodingmachine/laravel-universal-service-provider/): Use container-interop's service-providers into any [Laravel](http://laravel.com/) application.
- [Simplex](https://github.com/mnapoli/simplex): A [Pimple 3](https://github.com/silexphp/Pimple) fork with full [container-interop](https://github.com/container-interop/container-interop) compliance and cross-framework service-provider support.
- [Service provider bridge bundle](https://github.com/thecodingmachine/service-provider-bridge-bundle): Use container-interop's service-providers into a Symfony container.
- [Yaco](https://github.com/thecodingmachine/yaco): A compiler that generates container-interop compliant containers. Yaco can consume service-providers.

### Packages providing *service providers*

- [DBAL Module](https://github.com/thecodingmachine/dbal-universal-module): A module integrating [Doctrine DBAL](http://www.doctrine-project.org/projects/dbal.html) in an application using a service provider.
- [Doctrine Annotations Module](https://github.com/thecodingmachine/doctrine-annotations-universal-module): A service provider for Doctrine's annotation reader.
- [Glide Module](https://github.com/mnapoli/glide-module): A module integrating Glide in an application using a service provider.
- [PSR-6 to Doctrine cache bridge module](https://github.com/thecodingmachine/psr-6-doctrine-bridge-universal-module): A service provider providing a Doctrine cache provider wrapping a PSR-6 cache pool.
- [Slim-framework Module](https://github.com/thecodingmachine/slim-service-provider): A module integrating Slim framework v3 using a service provider.
- [Stash Module](https://github.com/thecodingmachine/stash-universal-module): A service provider for the Stash PSR-6 caching library.
- [Stratigility Module](https://github.com/thecodingmachine/stratigility-harmony): A service provider for the Stratigility PSR-7 middleware.
- [Twig Module](https://github.com/thecodingmachine/twig-universal-module): A service provider for the Twig templating library.
- [Whoops PSR-7 Middleware Module](https://github.com/thecodingmachine/whoops-middleware-universal-module): a service provider for the [Whoops](https://filp.github.io/whoops/) [PSR-7 middleware](https://github.com/franzliedke/whoops-middleware).

## Best practices

### Managing configuration

The service created by a factory should only depend on the input parameters of the factory (`$container` and `$getPrevious`).
If the factory needs to fetch parameters, those should be fetched from the container directly.

```php
class MyServiceProvider implements ServiceProviderInterface
{
    public function getFactories()
    {
        return [
            'logger' => [ MyServiceProvider::class, 'createLogger' ],
        ];
    }
    
    public function getExtensions()
    {
        return [];
    }

    public static function createLogger(ContainerInterface $container)
    {
        // The path to the log file is fetched from the container, not from the service provider state.
        return new FileLogger($this->container->get('logFilePath'));
    }
}
```

## FAQ

### Why does the service provider not configure the container instead of returning entries?

Service providers usually take a container and configure it (e.g. in Pimple). The problem is that it requires the container to expose methods for configuration. That's an impossible requirement in a standard because all containers have a different API for configuration and they could never be made to implement the same.

These service providers provide factories for each container entry it provides. They do not require configuration methods on containers, so they can be made compatible with all/most of them. Each container entry is, in the end, just a callable to invoke, which most containers can do.

### If everything is standardized is there a point to having many container implementations anymore?

The goal of [`container-interop/container-interop`](https://github.com/container-interop/container-interop) is to decouple frameworks (or sometimes libraries) from containers, so it is meant to be used mainly **by frameworks**. End users (i.e. developers) can still choose their favorite containers and make use of all their specific features.

The goal of this package (standard configuration) is to decouple modules from containers, so it is meant to be used **by developers writing modules**. End users (i.e. developers) can still choose their favorite containers and make use of all their specific features.

Developers are encouraged to continue using their containers like before. However modules can now become reusable accross frameworks by using this standard configuration format.

## Puli integration

The Puli integration is completely optional and not required to use this standard. It is only here to facilitate usage with Puli.

This package provides a [Puli *binding type*](http://docs.puli.io/en/latest/discovery/getting-started.html): `container-interop/service-provider`. Modules using Puli and implementing this standard can register service providers (fully qualified class names) through this binding type.

This way, frameworks or applications based on Puli can discover service providers automatically.

To register your service provider, simply use Puli's `bind` command:

```sh
puli bind --class Acme\\Foo\\MyServiceProvider container-interop/service-provider
```
