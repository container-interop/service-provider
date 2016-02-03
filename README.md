# Container-agnostic service providers

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
    
    public static function createMyService(ContainerInterface $container)
    {
        $dependency = $container->get('my_other_service');
    
        return new MyService($dependency);
    }
}
```

The `getServices()` static method must return a list of all container entries the service provider wishes to register:

- the key is the entry name
- the value is the name of the static method that will return the entry, aka the **factory method**

Factory methods must be public and static in the service provider class.

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
