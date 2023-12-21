# Service Provider Interface

This document describes a common interface for service providers.

Service providers are classes that provide service definitions to a [PSR-11][] container.

This PSR depends on the PSR-11 [Container Interface][]. Service providers operate on PSR-11 compatible containers.

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this document are to be
interpreted as described in [RFC 2119][].

[PSR-11]: https://www.php-fig.org/psr/psr-11/
[RFC 2119]: http://tools.ietf.org/html/rfc2119
[Container Interface]: https://www.php-fig.org/psr/psr-11/#31-psrcontainercontainerinterface

## 1. Specification

### 1.1. Basics

A service provider allows defining services and registering them with a [PSR-11][] container. This interface standardizes how frameworks and libraries declare service providers.

#### 1.1.2. Service Identifiers

A service identifier is a string that uniquely identifies a service within a container. A service identifier MUST be a legal PHP string identifier, as defined by [PSR-11][], cited here for reference:

> An entry identifier is any PHP-legal string of at least one character that uniquely identifies an item within a container. An entry identifier is an opaque string, so callers SHOULD NOT assume that the structure of the string carries any semantic meaning.

#### 1.1.3. Providing Services

The `Psr\Provider\ServiceProviderInterface` exposes two methods:

- `getFactories`: Returns factories for creating services.
- `getExtensions`: Returns extensions for modifying existing services.

### 1.2. Factories

A factory is a callable that accepts a container and returns a service. 

The `getFactories` method MUST return an associative array with the service identifier as the key and the factory as the value.

Factories have the following signature:

```php
function(Psr\Container\ContainerInterface $container): mixed
```

A factory MUST return a newly created service instance.

A factory MAY return `null` for a service - this SHOULD NOT be treated as an error, as other services may have nullable dependencies.

A factory SHOULD NOT cache anything or store state. Caching and state are the responsibility of the container.

### 1.3. Extensions

An extension is a callable for modifying an existing service.

The `getExtensions` method returns an associative array with the service identifier as the key and the extension as the value.

Extensions have the following signature:

```php
function(Psr\Container\ContainerInterface $container, mixed $service): mixed
```

Where `$service` is the existing service instance.

The `$service` parameter MAY be typehinted as required by the extension, and MAY be nullable.

An extension MUST return the modified service instance.

An extension MAY return `null` for a service - this SHOULD NOT be treated as an error, as other services may have nullable dependencies, and (as stated in section 1.3) the existing service could intentionally be `null`.

### 1.4. Dependencies

Factories and extensions MAY declare their dependencies by implementing the `ServiceDefinitionInterface`:

```php
public function getDependencies(): array
```

This allows containers to validate dependencies when registering services.

## 2. Interfaces

### 2.1. `Psr\Provider\ServiceProviderInterface`

Defines the service provider interface.

### 2.2. `Psr\Provider\ServiceDefinitionInterface` 

Allows declaring service dependencies. Implemented by factories and extensions.

### 2.3. `Psr\Provider\FactoryDefinitionInterface`

A factory that declares dependencies. (Extends `ServiceDefinitionInterface`.)

### 2.4. `Psr\Provider\ExtensionDefinitionInterface`

An extension that declares dependencies. (Extends `ServiceDefinitionInterface`.)

## 3. Package

The interfaces are provided by the [psr/provider](https://packagist.org/packages/psr/provider) package.

Packages providing PSR provider implementations SHOULD declare providing `psr/provider-implementation` `1.0.0`.

TODO ^ is this relevant? do consumers need to require `psr/provider-implementation` for any reason?

## 4. Usage Recommendations 

### 4.1. General

As per [PSR-11 section 1.3][]:

- Service identifiers SHOULD NOT be passed into services.

- Service definitions SHOULD declare dependencies via constructor arguments rather than resolving them from the container.

[PSR-11 section 1.3]: https://www.php-fig.org/psr/psr-11/#13-recommended-usage

### 4.2. Service State

Service providers SHOULD NOT maintain state or cache container data internally. Maintaining state and caching is the responsibility of the container consuming the service provider.

When a factory needs to access a parameter, it should fetch that from the `$container` rather than caching it internally.

TODO ^ this elaborates on what was already stated in section 1.2. Factories - should we move these paragraphs there and drop this section? or remove the paragraph from section 1.2 and save this information for (this) section 4. Usage Recommendations?

### 4.2. Idempotency  

Calling a factory or extension multiple times with the same container instance SHOULD result in the same service being created each time. Factories and extensions SHOULD NOT maintain internal state that modifies the returned service.

### 4.3. Declaring Dependencies

Factories and extensions SHOULD declare their dependencies through `FactoryDefinitionInterface` and `ExtensionDefinitionInterface` rather than directly accessing the container. This allows containers to validate dependencies during configuration.
