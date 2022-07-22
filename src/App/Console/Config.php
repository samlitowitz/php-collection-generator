<?php

namespace PhpCollectionGenerator\App\Console;

use JsonSerializable;
use PhpCollectionGenerator\App\Console\Config\Type;

final class Config implements JsonSerializable
{
	private array $types = [];

	private function __construct(array $types = [])
	{
		$this->setTypes($types);
	}

	public static function initialize(string $json): Config
	{
		$raw = \json_decode($json, true);
		[
			'types' => $rawTypes,
		] = $raw;

		$types = [];
		foreach ($rawTypes as $rawType) {
			$types[] = Type::arrayDeserialize($rawType);
		}

		return new Config($types);
	}

	public function jsonSerialize()
	{
		return [
			'types' => $this->getTypes(),
		];
	}

	public function getTypes(): array
	{
		return $this->types;
	}

	public function setTypes(array $types): void
	{
		$this->types = $types;
	}
}
