<?php

declare(strict_types=1);

namespace PhpCollectionGenerator\App\Console\Config;

use JsonSerializable;

use function call_user_func_array;
use function ucfirst;

final class Type implements JsonSerializable
{
    /** @var string */
    private $itemFQN;
    /** @var string */
    private $namespace;
    /** @var string */
    private $className;

    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function arrayDeserialize(array $input): Type
    {
        $type = new Type();
        foreach ($input as $prop => $value) {
            // @phpstan-ignore argument.type
            call_user_func_array([$type, 'set' . ucfirst($prop)], [$value]);
        }
        return $type;
    }

    public function jsonSerialize()
    {
        return [
            'itemFQN' => $this->getItemFQN(),
            'namespace' => $this->getNamespace(),
            'className' => $this->getClassName(),
        ];
    }

    public function getItemFQN(): string
    {
        return $this->itemFQN;
    }

    public function setItemFQN(string $itemFQN): void
    {
        $this->itemFQN = $itemFQN;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }
}
