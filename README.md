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
```shell
./vendor/bin/php-collection-generator --config /path/to/php-collection-generator.json generate /path/to/output/dir
```

### Configuration
```json
{
	"types": [
		{
			"itemFQN": "Type", // Fully qualified type name the collection will support
			"namespace": "PhpCollectionGenerator\\App\\Console\\Config", // Namespace the generated collection will belong to
			"className": "TypeCollection" // Class name the generated collection have
		} // Supports multiple typed collections in a single configuration
	]
}
```

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
      2. tabs vs spaces
