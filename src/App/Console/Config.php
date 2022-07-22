<?php

namespace PhpCollectionGenerator\App\Console;

use JsonSerializable;
use PhpCollectionGenerator\App\Console\Config\Type;
use PhpCollectionGenerator\App\Console\Config\TypeCollection;

final class Config implements JsonSerializable
{
	private TypeCollection $types;

	private function __construct(?TypeCollection $types = null)
	{
		$this->setTypes($types ?? new TypeCollection());
	}

	public static function initialize(string $json): Config
	{
		$raw = \json_decode($json, true);
		[
			'types' => $rawTypes,
		] = $raw;

		$types = new TypeCollection();
		foreach ($rawTypes as $rawType) {
			$types->add(Type::arrayDeserialize($rawType));
		}

		return new Config($types);
	}

	public function jsonSerialize()
	{
		return [
			'types' => $this->getTypes(),
		];
	}

	public function getTypes(): TypeCollection
	{
		return $this->types;
	}

	public function setTypes(TypeCollection $types): void
	{
		$this->types = $types;
	}
}
