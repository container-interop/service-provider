# Container-agnostic service providers

**Draft for submission in container-interop, work in progress!**

## Usage

To declare a service provider, simply implement the `ServiceProvider` interface.

```php
use Interop\Container\ServiceProvider\ServiceProvider;

class MyServiceProvider implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'my_service' => 'createMyService',
        ];
    }
    
    public static function createMyService(ContainerInterface $container, $previous = null)
    {
        $dependency = $container->get('my_other_service');
    
        return new MyService($dependency);
    }
}
```

The `getServices()` static method must return a list of all container entries the service provider wishes to register:

- the key is the entry name
- the value is the name of the static method that will return the entry, aka the **factory method**

Factory methods must be public and static in the service provider class. They accept the following parameters:

- the container (instance of `Interop\Container\ContainerInterface`)
- the previous entry if overriding a previous entry, or `null` if not

There is no difference between defining an entry from scratch or overriding/extending an entry. Factory methods always get the `$previous` value for the entry, it is up to you to *use it or ignore it* if it's not `null`.

If know you will be ignoring the `$previous` value, you can omit it from the parameters.

### Values (aka parameters)

A service provider can provide PHP objects (services) as well as any value. Simply return the value you wish from factory methods.

### Aliases

To alias a container entry to another, you can get the aliased entry from the container and return it:

```php
class MyServiceProvider implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'my_service' => 'createMyService',
            'alias' => 'resolveAlias',
        ];
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
class A implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'foo' => 'getFoo',
        ];
    }
    
    public static function getFoo()
    {
        return 'abc';
    }
}
```

Module B:

```php
class B implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'foo' => 'getFoo',
        ];
    }
    
    public static function getFoo()
    {
        return 'def';
    }
}
```

If you register the service providers in the correct order in your container (A first, then B), then the entry `foo` will be `'def'` because B's definition will override A's.

### Entry extension

Extending an entry before it is returned by the container is very similar to overriding it.

Module A:

```php
class A implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'logger' => 'getLogger',
        ];
    }
    
    public static function getLogger()
    {
        return new Logger;
    }
}
```

Module B:

```php
class B implements ServiceProvider
{
    public static function getServices()
    {
        return [
            'logger' => 'getLogger',
        ];
    }
    
    public static function getLogger(ContainerInterface $container, $previous = null)
    {
        // Register a new log handler
        $previous->addHandler(new SyslogHandler());
    
        // Return the object that we modified
        return $previous;
    }
}
```

If you register the service providers in the correct order in your container (A first, then B), the logger will be first created by `A` then a new handler will be registered on it by `B`.
