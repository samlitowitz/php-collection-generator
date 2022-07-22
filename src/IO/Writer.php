<?php

namespace PhpCollectionGenerator\IO;

interface Writer {
	public function write(string $d): int;
}
