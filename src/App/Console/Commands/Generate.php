<?php

declare(strict_types = 1);

namespace PhpCollectionGenerator\App\Console\Commands;

use InvalidARgumentException;
use PhpCollectionGenerator\App\Console\CLI;
use PhpCollectionGenerator\App\Console\Config;
use PhpCollectionGenerator\App\Console\Config\Type;
use PhpCollectionGenerator\Collection\Generator;
use PhpCollectionGenerator\OS\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class Generate extends Command
{
	protected static $defaultName = 'generate';
	protected static $defaultDescription = 'Generate PHP collection implementations for a set of types';
	private const OUTPUT_DIR_ARG = 'output-dir';

	protected function configure()
	{
		$this
			->addArgument(
				self::OUTPUT_DIR_ARG,
				InputArgument::REQUIRED,
				'Output directory'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$outputDirectory = $input->getArgument(self::OUTPUT_DIR_ARG);

		$configFileName = $input->getOption(CLI::CONFIG_OPT);
		$rawConfigData = \file_get_contents($configFileName);
		if ($rawConfigData === false) {
			throw new InvalidArgumentException(
				sprintf(
					'unable to load config %s',
					$configFileName
				)
			);
		}
		$config = Config::initialize($rawConfigData);
		$types = $config->getTypes();

		/** @var Type $type */
		foreach ($types as $type) {
			$outputFile = File::openFile($outputDirectory . '/' . $type->getClassName() . '.php', 'w+');
			try {
				$gen = new Generator($type, $outputFile);
				$gen->generate();
			} finally {
				$outputFile->close();
			}
		}

		return Command::SUCCESS;
	}
}
