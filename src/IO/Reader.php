<?php

namespace PhpCollectionGenerator\IO;

interface Reader {
	/**
	 * Read up to $n bytes.
	 * If no bytes can be read throw a PhpCollectionGenerator\IO\EndOfFileException.
	 */
	public function read(int $n): string;
}
