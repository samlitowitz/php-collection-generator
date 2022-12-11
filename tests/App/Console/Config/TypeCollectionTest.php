<?php

namespace PhpCollectionGenerator\Tests\App\Console\Config;

use PhpCollectionGenerator\App\Console\Config\Type;
use PhpCollectionGenerator\App\Console\Config\TypeCollection;
use PHPUnit\Framework\TestCase;

final class TypeCollectionTest extends TestCase
{
	public function testFromArray(): void
	{
		// Arrange
		$expectedTypeCount = 5;
		$expectedTypes = [];
		for ($i = 0; $i < $expectedTypeCount; $i++) {
			$expectedTypes[] = Type::arrayDeserialize([
				'itemFQN' => 'ItemFQN' . $i,
				'namespace' => 'Namespace' . $i,
				'className' => 'ClassName' . $i,
			]);
		}

		// Act
		$collection = TypeCollection::fromArray($expectedTypes);

		// Assert
		$this->assertEqualsCanonicalizing($expectedTypes, $collection->toArray());
	}

	public function testCountable(): void
	{
		// Arrange
		$expectedTypeCount = 5;
		$expectedTypes = [];
		for ($i = 0; $i < $expectedTypeCount; $i++) {
			$expectedTypes[] = Type::arrayDeserialize([
				'itemFQN' => 'ItemFQN' . $i,
				'namespace' => 'Namespace' . $i,
				'className' => 'ClassName' . $i,
			]);
		}

		// Act
		$collection = TypeCollection::fromArray($expectedTypes);

		// Assert
		$this->assertCount($expectedTypeCount, $collection);
	}

	public function testIterator(): void
	{
		// Arrange
		$expectedTypeCount = 5;
		$expectedTypes = [];
		for ($i = 0; $i < $expectedTypeCount; $i++) {
			$expectedTypes[] = Type::arrayDeserialize([
				'itemFQN' => 'ItemFQN' . $i,
				'namespace' => 'Namespace' . $i,
				'className' => 'ClassName' . $i,
			]);
		}

		// Act
		$collection = TypeCollection::fromArray($expectedTypes);

		// Assert
		foreach ($collection as $i => $type) {
			$this->assertEqualsCanonicalizing($expectedTypes[$i], $type);
		}
		$collection->rewind();
		$this->assertEqualsCanonicalizing($expectedTypes[0], $collection->current());
	}
}
