# PHP Collection Generator

Generate type-safe PHP collections!

## Table of Contents

1. [Installation](#installation)
2. [Usage](#usage)
    1. [Configuration](#configuration)
3. [Collection Interfaces and Additional Methods](#collection-interfaces-and-additional-methods)
    1. [Interfaces](#interfaces)
4. [Future Features](#future-features)

## Installation

```shell
composer require --dev samlitowitz/php-collection-generator
```

## Usage

1. Create a [configuration](#configuration) targeting the type(s) you wish to generate a collection for.
2. Generate the desired collection(s) by running the following command

   ```shell
   ./vendor/bin/php-collection-generator --config /path/to/php-collection-generator.json generate /path/to/output/dir
   ```

### Configuration

A JSON schema for the configuration is available [here](assets/schema/configuration.json).
The [`TypeCollection`](src/App/Console/Config/TypeCollection.php) is generated using the
example [configuration](example/php-collection-generator.json).

## Collection Interfaces and Additional Methods

### Interfaces

The generated collection will implement the following interfaces

1. `\Countable`
2. `\Iterator`
3. `\JsonSerializable`

### Additional Methods

The generated collection includes the following methods in addition to those needed to fulfil the above interfaces

1. `public function toArray(): array`
2. `public function add(\Fully\Qualified\Namespace\Type ...$entities): void`

## Future Features

1. Output File Formatting
    1. Configuration options
        1. type-hint member variables
            1. inline
            2. as comment
