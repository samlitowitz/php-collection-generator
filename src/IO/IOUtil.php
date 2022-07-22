<?php

namespace PhpCollectionGenerator\IO;

final class IOUtil {
	public const BLOCK_SIZE = 1024;

	public function readAll(Reader $r): string
	{
		try {
			$content = '';
			for (
				$buf = $r->read(self::BLOCK_SIZE);
				strlen($buf) === self::BLOCK_SIZE;
				$buf = $r->read(self::BLOCK_SIZE)
			) {
				$content .= $buf;
			}
			return $content;
		} catch (EndOfFileException $e) {
			return $content;
		}
	}
}
