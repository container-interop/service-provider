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
- the value is the name of the static method that will return the entry

Methods that will return the entries must be on the service provider class, public and static.
