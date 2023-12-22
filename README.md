# Standard Service Providers

This project attempts to define a common standard for service providers, aka "bundles" or "modules" in various frameworks.

Service providers are classes that provide service definitions to a [PSR-11](https://www.php-fig.org/psr/psr-11/) container.

The PSR depends on the PSR-11 [Container Interface](https://www.php-fig.org/psr/psr-11/#31-psrcontainercontainerinterface). Service providers operate on PSR-11 compatible containers.

**Work in progress:** the project is currently experimental and is being tried in frameworks, containers and modules until considered viable. Until a 1.0.0 release, the code in this repository is not stable. Expect breaking changes between versions such as `0.1.x` and `0.2.0`.

## Proposal

Refer to the [current PSR draft](./PSR-XX-provider.md) for the concrete proposal.

## Background

Refer to the [PSR meta document](./PSR-XX-provider-meta.md) for the historical background of this proposal.

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
