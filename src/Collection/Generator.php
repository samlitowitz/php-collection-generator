<?php

namespace PhpCollectionGenerator\Collection;

use PhpCollectionGenerator\App\Console\Config\Type;
use PhpCollectionGenerator\IO\Writer;
use PhpParser\Comment\Doc;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Return_;
use PhpParser\PrettyPrinter\Standard;
use RuntimeException;

final class Generator
{
	private Type $type;
	private Writer $w;
	private const ITEMS_PROP_NAME = 'items';
	private const ITER_PROP_NAME = 'iter';

	public function __construct(Type $type, Writer $w)
	{
		$this->type = $type;
		$this->w = $w;
	}

	public function generate(): void
	{
		$itemsPropertyStmt = new Property(
			Class_::MODIFIER_PRIVATE,
			[
				new PropertyProperty(self::ITEMS_PROP_NAME)
			]
		);
		$itemsPropertyStmt->setDocComment(
			new Doc(
				sprintf(
					'/** @var %s $%s */',
					$this->type->getItemFQN(),
					self::ITEMS_PROP_NAME
				)
			)
		);
		$iterPropertyStmt = new Property(
			Class_::MODIFIER_PRIVATE,
			[
				new PropertyProperty(self::ITER_PROP_NAME)
			]
		);
		$iterPropertyStmt->setDocComment(
			new Doc(
				sprintf(
					'/** @var %s $%s */',
					'?int',
					self::ITER_PROP_NAME
				)
			)
		);
		$propertyStmts = [
			$itemsPropertyStmt,
			$iterPropertyStmt,
		];

		$fromArrayFnStmt = new ClassMethod(
			'fromArray',
			[
				'flags' => Class_::MODIFIER_PUBLIC + Class_::MODIFIER_STATIC,
				'returnType' => new Name('self'),
				'params' => [
					new Param(
						new Variable(self::ITEMS_PROP_NAME),
						new Array_([]),
						new Identifier('array')
					)
				],
				'stmts' => [
					new Expression(
						new Assign(
							new Variable('collection'),
							new New_(new Name('self'))
						)
					),
					new Foreach_(
						new Variable(self::ITEMS_PROP_NAME),
						new Variable('item'),
						[
							'stmts' => [
								new Expression(
									new MethodCall(
										new Variable('collection'),
										new Identifier('add'),
										[
											new Arg(
												new Variable('item')
											),
										]
									)
								),
							],
						],
					),
					new Return_(
						new Variable('collection')
					),
				],
			]
		);

		$fnStmts = [
			$fromArrayFnStmt,
		];

		$class = new Class_(
			$this->type->getClassName(),
			[
				'flags' => Class_::MODIFIER_FINAL,
				'stmts' => \array_merge($propertyStmts, $fnStmts),
			]
		);

		$namespace = new Namespace_(
			new Name($this->type->getNamespace()),
			[$class]
		);

		$prettyPrinter = new Standard();
		$code = $prettyPrinter->prettyPrintFile([$namespace]);
		$n = $this->w->write($code);
		if ($n !== strlen($code)) {
			throw new RuntimeException('Write failed: incomplete write');
		}
	}
}
