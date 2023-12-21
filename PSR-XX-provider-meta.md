# Service Provider Meta Document

This document provides background and reasoning for the Service Provider PSR.

## 1. Introduction

The Service Provider PSR aims to standardize how frameworks, containers, and libraries declare and consume service providers. Service providers are classes that define services for a container.

For containers, the goal is to standardize how they consume service providers. For libraries and modules, the goal is to standardize how they provide services in a portable way.

By standardizing service providers, modules and libraries can define services in a cross-framework way, avoiding the need to write a different provider for every container.

## 2. Background

The [container-interop group][] explored different approaches for standardizing service definitions:

[container-interop group]: https://github.com/container-interop

### 2.1. Standard Definitions

Defining standard PHP interfaces to represent service definitions. This was implemented in the [definition interop][] project.

Limitations of this approach:

- Complex to understand and use.
- Still requires mapping to different container APIs.

[definition interop]: https://github.com/container-interop/definition-interop

### 2.2. Standard Configuration Format

Defining a standard configuration format like XML.

This was not formally specified but discussed.

Limitations of this approach:

- Implementation burden is high.
- Forces all containers to support many complex definition features.

### 2.3. Standard Service Providers

Defining standard service providers to directly provide service factories and extensions.

This approach was selected for its simplicity compared to the alternatives.

## 3. Goals

The goals of standardizing service providers are:

- Allow modules/libraries to define services in a portable way.
- Easy implementation for containers.
- Promote separation of configuration from application code.

## 4. Rationale

Service providers usually take a container and configure it directly, via a container-specific configuration API.

The configuration API is a core part of what distinguishes container implementations. Standardizing configuration would erode the differences between containers, which would remove much of the reason for their existence. The goal here is interoperability through a common format, not removing the value of different containers.

The goal is to decouple **modules** from containers through a standard format. This allows modules to be reusable across frameworks. The choice of which container implementations to use is still open to developers.

## 5. Purpose

TODO ^ Purpose, Intent, Positioning, Relationship with existing ecosystem, or something different?

The service providers defined by this PSR are intended as a "lowest common denominator" format. They are NOT intended to compete with or replace the developer experience of using service providers in full-featured DI containers and frameworks.

Rather, the goal is to achieve interoperability between various containers and frameworks. This could be achieved by:

- DI containers implementing support for consuming and exporting PSR service providers.
- Dedicated "service provider builders" which provide more convenient and opinionated APIs, and generate PSR service providers.

While hand-authoring PSR service providers is possible, the expectation is that developers will use the service definition facilities native to their preferred container or framework, while interoperability will be achieved behind the scenes through support for PSR providers.

The choice of which container and provider implementations to use for application services is left open to developers.

## 6. Service Discovery

Integration with projects like Puli was explored, for automatic discovery and binding of service providers.

Puli is EOL by now, but served as proof of concept with regards to the potential for service discovery, whether this be framework-specific, or via a future standard.

Service discovery (as a feature of the standard) is outside the scope of this PSR.
