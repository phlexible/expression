<?php

/*
 * This file is part of the phlexible package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Component\Expression\Test\Serializer;

use Phlexible\Component\Expression\Serializer\ArrayExpressionSerializer;
use PHPUnit\Framework\TestCase;
use Webmozart\Expression\Expr;

/**
 * Array expression serializer test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ArrayExpressionSerializerTest extends TestCase
{
    public function testSerializeAnd()
    {
        $expr = Expr::key('firstname', Expr::equals('John'))
            ->andKey('lastname', Expr::equals('Doe'));

        $serializer = new ArrayExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            array(
                'logic' => 'and',
                'conjuncts' => array(
                    array('selector' => 'key', 'key' => 'firstname', 'expression' => array('constraint' => 'equals', 'value' => 'John')),
                    array('selector' => 'key', 'key' => 'lastname', 'expression' => array('constraint' => 'equals', 'value' => 'Doe')),
                ),
            ),
            $data
        );
    }

    public function testSerializeOr()
    {
        $expr = Expr::key('firstname', Expr::equals('John'))
            ->orKey('lastname', Expr::equals('Doe'));

        $serializer = new ArrayExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            array(
                'logic' => 'or',
                'disjuncts' => array(
                    array('selector' => 'key', 'key' => 'firstname', 'expression' => array('constraint' => 'equals', 'value' => 'John')),
                    array('selector' => 'key', 'key' => 'lastname', 'expression' => array('constraint' => 'equals', 'value' => 'Doe')),
                ),
            ),
            $data
        );
    }

    public function testSerializeNot()
    {
        $expr = Expr::not(Expr::key('firstname', Expr::equals('John')));

        $serializer = new ArrayExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            array(
                'logic' => 'not',
                'negatedExpression' => array('selector' => 'key', 'key' => 'firstname', 'expression' => array('constraint' => 'equals', 'value' => 'John')),
            ),
            $data
        );
    }

    public function testSerializeJunction()
    {
        $expr = Expr::key('username', Expr::equals('jdoe'))->andX(
            Expr::key('firstname', Expr::equals('John'))
                ->orKey('lastname', Expr::equals('Doe'))
        );

        $serializer = new ArrayExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            array(
                'logic' => 'and',
                'conjuncts' => array(
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'equals', 'value' => 'jdoe')),
                    array('logic' => 'or', 'disjuncts' => array(
                        array('selector' => 'key', 'key' => 'firstname', 'expression' => array('constraint' => 'equals', 'value' => 'John'),
                        ),
                        array('selector' => 'key', 'key' => 'lastname', 'expression' => array('constraint' => 'equals', 'value' => 'Doe'),
                        ),
                    )),
                ),
            ),
            $data
        );
    }

    public function testSerializeConstraints()
    {
        $expr = Expr::key('username', Expr::equals('jdoe'))
            ->andKey('username', Expr::notEquals('xdoe'))
            ->andKey('username', Expr::same('jdoe'))
            ->andKey('username', Expr::notSame('xdoe'))
            ->andKey('username', Expr::startsWith('jd'))
            ->andKey('username', Expr::endswith('oe'))
            ->andKey('username', Expr::contains('do'))
            ->andKey('username', Expr::matches('/do/'))
            ->andKey('username', Expr::in(array('jdoe', 'xdoe')))
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
            array(
                'logic' => 'and',
                'conjuncts' => array(
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'equals', 'value' => 'jdoe')),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'notEquals', 'value' => 'xdoe')),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'same', 'value' => 'jdoe')),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'notSame', 'value' => 'xdoe')),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'startsWith', 'value' => 'jd')),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'endsWith', 'value' => 'oe')),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'contains', 'value' => 'do')),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'matches', 'value' => '/do/')),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'in', 'value' => array('jdoe', 'xdoe'))),
                    array('selector' => 'key', 'key' => 'properties', 'expression' => array('constraint' => 'keyExists', 'value' => 'xxx')),
                    array('selector' => 'key', 'key' => 'properties', 'expression' => array('constraint' => 'keyNotExists', 'value' => 'yyy')),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'same', 'value' => null)),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'notSame', 'value' => null)),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'isEmpty')),
                    array('selector' => 'key', 'key' => 'username', 'expression' => array('logic' => 'not', 'negatedExpression' => array('constraint' => 'isEmpty'))),
                    array('selector' => 'key', 'key' => 'logins', 'expression' => array('constraint' => 'greaterThan', 'value' => 1)),
                    array('selector' => 'key', 'key' => 'logins', 'expression' => array('constraint' => 'greaterThanEqual', 'value' => 2)),
                    array('selector' => 'key', 'key' => 'logins', 'expression' => array('constraint' => 'lessThan', 'value' => 3)),
                    array('selector' => 'key', 'key' => 'logins', 'expression' => array('constraint' => 'lessThanEqual', 'value' => 4)),
                ),
            ),
            $data
        );
    }

    public function testDeserializeAnd()
    {
        $data = array(
            'logic' => 'and',
            'conjuncts' => array(
                array('selector' => 'key', 'key' => 'firstname', 'expression' => array('constraint' => 'equals', 'value' => 'John')),
                array('selector' => 'key', 'key' => 'lastname', 'expression' => array('constraint' => 'equals', 'value' => 'Doe')),
            ),
        );

        $serializer = new ArrayExpressionSerializer();
        $expr = $serializer->deserialize($data);

        $this->assertSame('firstname=="John" && lastname=="Doe"', (string) $expr);
    }

    public function testDeserializeOr()
    {
        $data = array(
            'logic' => 'or',
            'disjuncts' => array(
                array('selector' => 'key', 'key' => 'firstname', 'expression' => array('constraint' => 'equals', 'value' => 'John')),
                array('selector' => 'key', 'key' => 'lastname', 'expression' => array('constraint' => 'equals', 'value' => 'Doe')),
            ),
        );

        $serializer = new ArrayExpressionSerializer();
        $expr = $serializer->deserialize($data);

        $this->assertSame('firstname=="John" || lastname=="Doe"', (string) $expr);
    }

    public function testDeserializeNot()
    {
        $data = array(
            'logic' => 'not',
            'negatedExpression' => array('selector' => 'key', 'key' => 'firstname', 'expression' => array('constraint' => 'equals', 'value' => 'John')),
        );

        $serializer = new ArrayExpressionSerializer();
        $expr = $serializer->deserialize($data);

        $this->assertSame('not(firstname=="John")', (string) $expr);
    }

    public function testDeserializeAndWithNestedExpression()
    {
        $data = array(
            'logic' => 'and',
            'conjuncts' => array(
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'equals', 'value' => 'jdoe')),
                array('logic' => 'or', 'disjuncts' => array(
                    array('selector' => 'key', 'key' => 'firstname', 'expression' => array('constraint' => 'startsWith', 'value' => 'Joh')),
                    array('selector' => 'key', 'key' => 'lastname', 'expression' => array('constraint' => 'endsWith', 'value' => 'oe')),
                )),
            ),
        );

        $serializer = new ArrayExpressionSerializer();
        $expr = $serializer->deserialize($data);

        $this->assertSame('username=="jdoe" && (firstname.startsWith("Joh") || lastname.endsWith("oe"))', (string) $expr);
    }

    public function testDeserializeComparisons()
    {
        $data = array(
            'logic' => 'and',
            'conjuncts' => array(
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'equals', 'value' => 'jdoe')),
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'notEquals', 'value' => 'xdoe')),
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'same', 'value' => 'jdoe')),
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'notSame', 'value' => 'xdoe')),
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'startsWith', 'value' => 'Joh')),
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'endsWith', 'value' => 'oe')),
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'contains', 'value' => 'do')),
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'matches', 'value' => '/test/')),
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'in', 'value' => array('jdoe', 'xdoe'))),
                array('selector' => 'key', 'key' => 'properties', 'expression' => array('constraint' => 'keyExists', 'value' => 'xxx')),
                array('selector' => 'key', 'key' => 'properties', 'expression' => array('constraint' => 'keyNotExists', 'value' => 'yyy')),
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'null')),
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'notNull')),
                array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'isEmpty')),
                array('logic' => 'not', 'negatedExpression' => array('selector' => 'key', 'key' => 'username', 'expression' => array('constraint' => 'isEmpty'))),
                array('selector' => 'key', 'key' => 'logins', 'expression' => array('constraint' => 'greaterThan', 'value' => 1)),
                array('selector' => 'key', 'key' => 'logins', 'expression' => array('constraint' => 'greaterThanEqual', 'value' => 2)),
                array('selector' => 'key', 'key' => 'logins', 'expression' => array('constraint' => 'lessThan', 'value' => 3)),
                array('selector' => 'key', 'key' => 'logins', 'expression' => array('constraint' => 'lessThanEqual', 'value' => 4)),
            ),
        );

        $serializer = new ArrayExpressionSerializer();
        $expr = $serializer->deserialize($data);

        $this->assertSame('username=="jdoe" && username!="xdoe" && username==="jdoe" && username!=="xdoe" && username.startsWith("Joh") && username.endsWith("oe") && username.contains("do") && username.matches("/test/") && username.in("jdoe", "xdoe") && properties.keyExists("xxx") && properties.keyNotExists("yyy") && username===null && username!==null && username.empty() && not(username.empty()) && logins>1 && logins>=2 && logins<3 && logins<=4', (string) $expr);
    }
}
