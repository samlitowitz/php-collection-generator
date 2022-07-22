<?php

namespace PhpCollectionGenerator\App\Console\Config;

final class TypeCollection implements \Countable, \Iterator
{
    /** @var []Type $items */
    private $items;
    /** @var ?int $iter */
    private $iter;
    public static function fromArray(array $items = array()) : self
    {
        $collection = new self();
        foreach ($items as $item) {
            $collection->add($item);
        }
        return $collection;
    }
    public function current() : ?Type
    {
        if ($this->iter === null) {
            return null;
        }
        if (!\array_key_exists($this->iter, $this->items)) {
            return null;
        }
        return $this->items[$this->iter];
    }
    public function next()
    {
        if (!$this->valid()) {
            return;
        }
        $this->iter++;
    }
    public function key() : ?int
    {
        return $this->iter;
    }
    public function valid() : bool
    {
        return $this->current() !== null;
    }
    public function rewind() : void
    {
        if ($this->count() === 0) {
            $this->iter = null;
            return;
        }
        $this->iter = 0;
    }
    public function count() : int
    {
        return \count($this->items);
    }
    public function add(Type $entity)
    {
        $this->items[] = $entity;
    }
}