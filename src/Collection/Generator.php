<?php

namespace PhpCollectionGenerator\Collection;

use PhpCollectionGenerator\App\Console\Config\Type;
use PhpCollectionGenerator\IO\Writer;
use PhpParser\Comment\Doc;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
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
                new PropertyProperty(
                    self::ITEMS_PROP_NAME,
                    new Array_()
                ),
            ],
            [
                new Doc(
                    sprintf(
                        '/** @var []%s $%s */',
                        $this->type->getItemFQN(),
                        self::ITEMS_PROP_NAME
                    )
                ),
            ]
        );
        $iterPropertyStmt = new Property(
            Class_::MODIFIER_PRIVATE,
            [
                new PropertyProperty(self::ITER_PROP_NAME),
            ],
            [
                new Doc(
                    sprintf(
                        '/** @var %s $%s */',
                        '?int',
                        self::ITER_PROP_NAME
                    )
                ),
            ]
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

        $countFnStmt = new ClassMethod(
            'count',
            [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => new Identifier('int'),
                'stmts' => [
                    new Return_(
                        new FuncCall(
                            new Name\FullyQualified('count'),
                            [
                                new Arg(
                                    new PropertyFetch(
                                        new Variable('this'),
                                        new Identifier(self::ITEMS_PROP_NAME)
                                    )
                                ),
                            ]
                        )
                    ),
                ],
            ]
        );

        $jsonSerializeFnStmt = new ClassMethod(
            'jsonSerialize',
            [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => null,
                'stmts' => [
                    new Return_(
                        new MethodCall(
                            new Variable('this'),
                            new Identifier('toArray')
                        )
                    ),
                ],
            ]
        );

        $addFnStmt = new ClassMethod(
            'add',
            [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => new Identifier('void'),
                'params' => [
                    new Param(
                        new Variable('entities'),
                        null,
                        new Name($this->type->getItemFQN()),
                        false,
                        true
                    ),
                ],
                'stmts' => [
                    new Expression(
                        new FuncCall(
                            new Name\FullyQualified('array_push'),
                            [
                                new Arg(
                                    new PropertyFetch(
                                        new Variable('this'),
                                        new Identifier('items')
                                    )
                                ),
                                new Arg(
                                    new Variable('entities'),
                                    false,
                                    true
                                ),
                            ]
                        )
                    ),
                ],
            ]
        );

        $toArrayFnStmt = new ClassMethod(
            'toArray',
            [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => new Identifier('array'),
                'stmts' => [
                    new Return_(
                        new PropertyFetch(
                            new Variable('this'),
                            new Identifier('items')
                        )
                    ),
                ],
            ]
        );

        $fnStmts = array_merge(
            [
                $fromArrayFnStmt,
                $countFnStmt,
                $jsonSerializeFnStmt,
                $toArrayFnStmt,
                $addFnStmt,
            ],
            $this->iteratorFnStmts(),
        );

        $class = new Class_(
            $this->type->getClassName(),
            [
                'flags' => Class_::MODIFIER_FINAL,
                'implements' => [
                    new Name\FullyQualified('Countable'),
                    new Name\FullyQualified('Iterator'),
                    new Name\FullyQualified('JsonSerializable')
                ],
                'stmts' => \array_merge($propertyStmts, $fnStmts),
            ]
        );

        $namespace = new Namespace_(
            new Name($this->type->getNamespace()),
            [$class]
        );

        $prettyPrinter = new Standard(['shortArraySyntax' => true]);
        $code = $prettyPrinter->prettyPrintFile([$namespace]);
        $n = $this->w->write($code);
        if ($n !== strlen($code)) {
            throw new RuntimeException('Write failed: incomplete write');
        }
    }

    /**
     * @return array<Stmt>
     */
    private function iteratorFnStmts(): array
    {
        $currentFnStmt = new ClassMethod(
            'current',
            [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => new NullableType($this->type->getItemFQN()),
                'stmts' => [
                    new If_(
                        new Identical(
                            new PropertyFetch(
                                new Variable('this'),
                                new Identifier('iter')
                            ),
                            new ConstFetch(new Name('null'))
                        ),
                        [
                            'stmts' => [
                                new Return_(new ConstFetch(new Name('null'))),
                            ],
                        ]
                    ),
                    new If_(
                        new BooleanNot(
                            new FuncCall(
                                new Name\FullyQualified('array_key_exists'),
                                [
                                    new Arg(
                                        new PropertyFetch(
                                            new Variable('this'),
                                            new Identifier(self::ITER_PROP_NAME)
                                        )
                                    ),
                                    new Arg(
                                        new PropertyFetch(
                                            new Variable('this'),
                                            new Identifier(self::ITEMS_PROP_NAME)
                                        )
                                    ),
                                ]
                            )
                        ),
                        [
                            'stmts' => [
                                new Return_(new ConstFetch(new Name('null'))),
                            ],
                        ]
                    ),
                    new Return_(
                        new ArrayDimFetch(
                            new PropertyFetch(
                                new Variable('this'),
                                new Identifier(self::ITEMS_PROP_NAME)
                            ),
                            new PropertyFetch(
                                new Variable('this'),
                                new Identifier(self::ITER_PROP_NAME)
                            )
                        )
                    ),
                ],
            ]
        );

        $nextFnStmt = new ClassMethod(
            'next',
            [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => null,
                'stmts' => [
                    new If_(
                        new BooleanNot(
                            new MethodCall(
                                new Variable('this'),
                                new Identifier('valid')
                            )
                        ),
                        [
                            'stmts' => [
                                new Return_(null),
                            ],
                        ]
                    ),
                    new Expression(
                        new PostInc(
                            new PropertyFetch(
                                new Variable('this'),
                                new Identifier('iter')
                            )
                        )
                    )
                ],
            ]
        );

        $keyFnStmt = new ClassMethod(
            'key',
            [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => new NullableType('int'),
                'stmts' => [
                    new Return_(
                        new PropertyFetch(
                            new Variable('this'),
                            new Identifier('iter')
                        )
                    )
                ],
            ]
        );

        $rewindFnStmt = new ClassMethod(
            'rewind',
            [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => new Identifier('void'),
                'stmts' => [
                    new If_(
                        new Identical(
                            new MethodCall(
                                new Variable('this'),
                                new Identifier('count')
                            ),
                            new LNumber(0)
                        ),
                        [
                            'stmts' => [
                                new Expression(
                                    new Assign(
                                        new PropertyFetch(
                                            new Variable('this'),
                                            new Identifier('iter')
                                        ),
                                        new ConstFetch(new Name('null'))
                                    )
                                ),
                                new Return_(),
                            ],
                        ]
                    ),
                    new Expression(
                        new Assign(
                            new PropertyFetch(
                                new Variable('this'),
                                new Identifier('iter')
                            ),
                            new LNumber(0)
                        )
                    )
                ],
            ]
        );

        $validFnStmt = new ClassMethod(
            'valid',
            [
                'flags' => Class_::MODIFIER_PUBLIC,
                'returnType' => new Identifier('bool'),
                'stmts' => [
                    new Return_(
                        new NotIdentical(
                            new MethodCall(
                                new Variable('this'),
                                new Identifier('current')
                            ),
                            new ConstFetch(new Name('null'))
                        )
                    ),
                ],
            ]
        );

        return [
            $currentFnStmt,
            $nextFnStmt,
            $keyFnStmt,
            $rewindFnStmt,
            $validFnStmt,
        ];
    }
}
