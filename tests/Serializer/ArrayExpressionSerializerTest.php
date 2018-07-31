<?php

/*
 * This file is part of the phlexible expression package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Phlexible\Component\ExpressionTests\Serializer;

use Phlexible\Component\Expression\Serializer\ArrayExpressionSerializer;
use PHPUnit\Framework\TestCase;
use Webmozart\Expression\Expr;

/**
 * @covers \Phlexible\Component\Expression\Serializer\ArrayExpressionSerializer
 */
class ArrayExpressionSerializerTest extends TestCase
{
    public function testSerializeAnd(): void
    {
        $expr = Expr::key('firstname', Expr::equals('John'))
            ->andKey('lastname', Expr::equals('Doe'));

        $serializer = new ArrayExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            [
                'logic' => 'and',
                'conjuncts' => [
                    ['selector' => 'key', 'key' => 'firstname', 'expression' => ['constraint' => 'equals', 'value' => 'John']],
                    ['selector' => 'key', 'key' => 'lastname', 'expression' => ['constraint' => 'equals', 'value' => 'Doe']],
                ],
            ],
            $data
        );
    }

    public function testSerializeOr(): void
    {
        $expr = Expr::key('firstname', Expr::equals('John'))
            ->orKey('lastname', Expr::equals('Doe'));

        $serializer = new ArrayExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            [
                'logic' => 'or',
                'disjuncts' => [
                    ['selector' => 'key', 'key' => 'firstname', 'expression' => ['constraint' => 'equals', 'value' => 'John']],
                    ['selector' => 'key', 'key' => 'lastname', 'expression' => ['constraint' => 'equals', 'value' => 'Doe']],
                ],
            ],
            $data
        );
    }

    public function testSerializeNot(): void
    {
        $expr = Expr::not(Expr::key('firstname', Expr::equals('John')));

        $serializer = new ArrayExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            [
                'logic' => 'not',
                'negatedExpression' => ['selector' => 'key', 'key' => 'firstname', 'expression' => ['constraint' => 'equals', 'value' => 'John']],
            ],
            $data
        );
    }

    public function testSerializeJunction(): void
    {
        $expr = Expr::key('username', Expr::equals('jdoe'))->andX(
            Expr::key('firstname', Expr::equals('John'))
                ->orKey('lastname', Expr::equals('Doe'))
        );

        $serializer = new ArrayExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            [
                'logic' => 'and',
                'conjuncts' => [
                    [
                        'selector' => 'key',
                        'key' => 'username',
                        'expression' => ['constraint' => 'equals', 'value' => 'jdoe'],
                    ],
                    [
                        'logic' => 'or',
                        'disjuncts' => [
                            ['selector' => 'key', 'key' => 'firstname', 'expression' => ['constraint' => 'equals', 'value' => 'John']],
                            ['selector' => 'key', 'key' => 'lastname', 'expression' => ['constraint' => 'equals', 'value' => 'Doe']],
                        ],
                    ],
                ],
            ],
            $data
        );
    }

    public function testSerializeConstraints(): void
    {
        $expr = Expr::key('username', Expr::equals('jdoe'))
            ->andKey('username', Expr::notEquals('xdoe'))
            ->andKey('username', Expr::same('jdoe'))
            ->andKey('username', Expr::notSame('xdoe'))
            ->andKey('username', Expr::startsWith('jd'))
            ->andKey('username', Expr::endsWith('oe'))
            ->andKey('username', Expr::contains('do'))
            ->andKey('username', Expr::matches('/do/'))
            ->andKey('username', Expr::in(['jdoe', 'xdoe']))
            ->andKey('properties', Expr::keyExists('xxx'))
            ->andKey('properties', Expr::keyNotExists('yyy'))
            //->andTrue()
            //->andFalse()
            ->andKey('username', Expr::null())
            ->andKey('username', Expr::notNull())
            ->andKey('username', Expr::isEmpty())
            ->andKey('username', Expr::notEmpty())
            ->andKey('logins', Expr::greaterThan(1))
            ->andKey('logins', Expr::greaterThanEqual(2))
            ->andKey('logins', Expr::lessThan(3))
            ->andKey('logins', Expr::lessThanEqual(4));

        $serializer = new ArrayExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            [
                'logic' => 'and',
                'conjuncts' => [
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'equals', 'value' => 'jdoe']],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'notEquals', 'value' => 'xdoe']],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'same', 'value' => 'jdoe']],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'notSame', 'value' => 'xdoe']],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'startsWith', 'value' => 'jd']],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'endsWith', 'value' => 'oe']],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'contains', 'value' => 'do']],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'matches', 'value' => '/do/']],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'in', 'value' => ['jdoe', 'xdoe']]],
                    ['selector' => 'key', 'key' => 'properties', 'expression' => ['constraint' => 'keyExists', 'value' => 'xxx']],
                    ['selector' => 'key', 'key' => 'properties', 'expression' => ['constraint' => 'keyNotExists', 'value' => 'yyy']],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'same', 'value' => null]],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'notSame', 'value' => null]],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'isEmpty']],
                    ['selector' => 'key', 'key' => 'username', 'expression' => ['logic' => 'not', 'negatedExpression' => ['constraint' => 'isEmpty']]],
                    ['selector' => 'key', 'key' => 'logins', 'expression' => ['constraint' => 'greaterThan', 'value' => 1]],
                    ['selector' => 'key', 'key' => 'logins', 'expression' => ['constraint' => 'greaterThanEqual', 'value' => 2]],
                    ['selector' => 'key', 'key' => 'logins', 'expression' => ['constraint' => 'lessThan', 'value' => 3]],
                    ['selector' => 'key', 'key' => 'logins', 'expression' => ['constraint' => 'lessThanEqual', 'value' => 4]],
                ],
            ],
            $data
        );
    }

    public function testDeserializeAnd(): void
    {
        $data = [
            'logic' => 'and',
            'conjuncts' => [
                ['selector' => 'key', 'key' => 'firstname', 'expression' => ['constraint' => 'equals', 'value' => 'John']],
                ['selector' => 'key', 'key' => 'lastname', 'expression' => ['constraint' => 'equals', 'value' => 'Doe']],
            ],
        ];

        $serializer = new ArrayExpressionSerializer();
        $expr = $serializer->deserialize($data);

        $this->assertSame('firstname=="John" && lastname=="Doe"', (string) $expr);
    }

    public function testDeserializeOr(): void
    {
        $data = [
            'logic' => 'or',
            'disjuncts' => [
                ['selector' => 'key', 'key' => 'firstname', 'expression' => ['constraint' => 'equals', 'value' => 'John']],
                ['selector' => 'key', 'key' => 'lastname', 'expression' => ['constraint' => 'equals', 'value' => 'Doe']],
            ],
        ];

        $serializer = new ArrayExpressionSerializer();
        $expr = $serializer->deserialize($data);

        $this->assertSame('firstname=="John" || lastname=="Doe"', (string) $expr);
    }

    public function testDeserializeNot(): void
    {
        $data = [
            'logic' => 'not',
            'negatedExpression' => ['selector' => 'key', 'key' => 'firstname', 'expression' => ['constraint' => 'equals', 'value' => 'John']],
        ];

        $serializer = new ArrayExpressionSerializer();
        $expr = $serializer->deserialize($data);

        $this->assertSame('not(firstname=="John")', (string) $expr);
    }

    public function testDeserializeAndWithNestedExpression(): void
    {
        $data = [
            'logic' => 'and',
            'conjuncts' => [
                [
                    'selector' => 'key',
                    'key' => 'username',
                    'expression' => ['constraint' => 'equals', 'value' => 'jdoe'],
                ],
                [
                    'logic' => 'or',
                    'disjuncts' => [
                        ['selector' => 'key', 'key' => 'firstname', 'expression' => ['constraint' => 'startsWith', 'value' => 'Joh']],
                        ['selector' => 'key', 'key' => 'lastname', 'expression' => ['constraint' => 'endsWith', 'value' => 'oe']],
                    ],
                ],
            ],
        ];

        $serializer = new ArrayExpressionSerializer();
        $expr = $serializer->deserialize($data);

        $this->assertSame('username=="jdoe" && (firstname.startsWith("Joh") || lastname.endsWith("oe"))', (string) $expr);
    }

    public function testDeserializeComparisons(): void
    {
        $data = [
            'logic' => 'and',
            'conjuncts' => [
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'equals', 'value' => 'jdoe']],
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'notEquals', 'value' => 'xdoe']],
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'same', 'value' => 'jdoe']],
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'notSame', 'value' => 'xdoe']],
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'startsWith', 'value' => 'Joh']],
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'endsWith', 'value' => 'oe']],
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'contains', 'value' => 'do']],
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'matches', 'value' => '/test/']],
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'in', 'value' => ['jdoe', 'xdoe']]],
                ['selector' => 'key', 'key' => 'properties', 'expression' => ['constraint' => 'keyExists', 'value' => 'xxx']],
                ['selector' => 'key', 'key' => 'properties', 'expression' => ['constraint' => 'keyNotExists', 'value' => 'yyy']],
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'null']],
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'notNull']],
                ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'isEmpty']],
                ['logic' => 'not', 'negatedExpression' => ['selector' => 'key', 'key' => 'username', 'expression' => ['constraint' => 'isEmpty']]],
                ['selector' => 'key', 'key' => 'logins', 'expression' => ['constraint' => 'greaterThan', 'value' => 1]],
                ['selector' => 'key', 'key' => 'logins', 'expression' => ['constraint' => 'greaterThanEqual', 'value' => 2]],
                ['selector' => 'key', 'key' => 'logins', 'expression' => ['constraint' => 'lessThan', 'value' => 3]],
                ['selector' => 'key', 'key' => 'logins', 'expression' => ['constraint' => 'lessThanEqual', 'value' => 4]],
            ],
        ];

        $serializer = new ArrayExpressionSerializer();
        $expr = $serializer->deserialize($data);

        $this->assertSame('username=="jdoe" && username!="xdoe" && username==="jdoe" && username!=="xdoe" && username.startsWith("Joh") && username.endsWith("oe") && username.contains("do") && username.matches("/test/") && username.in("jdoe", "xdoe") && properties.keyExists("xxx") && properties.keyNotExists("yyy") && username===null && username!==null && username.empty() && not(username.empty()) && logins>1 && logins>=2 && logins<3 && logins<=4', (string) $expr);
    }
}
