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

use Phlexible\Component\Expression\Serializer\StringExpressionSerializer;
use PHPUnit\Framework\TestCase;
use Webmozart\Expression\Constraint\Contains;
use Webmozart\Expression\Constraint\EndsWith;
use Webmozart\Expression\Constraint\Equals;
use Webmozart\Expression\Constraint\GreaterThan;
use Webmozart\Expression\Constraint\GreaterThanEqual;
use Webmozart\Expression\Constraint\In;
use Webmozart\Expression\Constraint\IsEmpty;
use Webmozart\Expression\Constraint\KeyExists;
use Webmozart\Expression\Constraint\KeyNotExists;
use Webmozart\Expression\Constraint\LessThan;
use Webmozart\Expression\Constraint\LessThanEqual;
use Webmozart\Expression\Constraint\Matches;
use Webmozart\Expression\Constraint\NotEquals;
use Webmozart\Expression\Constraint\NotSame;
use Webmozart\Expression\Constraint\Same;
use Webmozart\Expression\Constraint\StartsWith;
use Webmozart\Expression\Expr;
use Webmozart\Expression\Logic\AndX;
use Webmozart\Expression\Logic\Not;
use Webmozart\Expression\Logic\OrX;
use Webmozart\Expression\Selector\Key;

/**
 * @covers \Phlexible\Component\Expression\Serializer\StringExpressionSerializer
 */
class StringExpressionSerializerTest extends TestCase
{
    public function testSerializeAnd(): void
    {
        $expr = Expr::key('firstname', Expr::equals('John'))
            ->andKey('lastname', Expr::equals('Doe'));

        $serializer = new StringExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            'and(key(firstname, equals("John")), key(lastname, equals("Doe")))',
            $data
        );
    }

    public function testSerializeOr(): void
    {
        $expr = Expr::key('firstname', Expr::equals('John'))
            ->orKey('lastname', Expr::equals('Doe'));

        $serializer = new StringExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            'or(key(firstname, equals("John")), key(lastname, equals("Doe")))',
            $data
        );
    }

    public function testSerializeNot(): void
    {
        $expr = Expr::not(Expr::key('firstname', Expr::equals('John')));

        $serializer = new StringExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            'not(key(firstname, equals("John")))',
            $data
        );
    }

    public function testSerializeJunction(): void
    {
        $expr = Expr::key('username', Expr::equals('jdoe'))->andX(
            Expr::key('firstname', Expr::equals('John'))
                ->orKey('lastname', Expr::equals('Doe'))
        );

        $serializer = new StringExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            'and(key(username, equals("jdoe")), or(key(firstname, equals("John")), key(lastname, equals("Doe"))))',
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

        $serializer = new StringExpressionSerializer();
        $data = $serializer->serialize($expr);

        $this->assertEquals(
            'and(key(username, equals("jdoe")), key(username, notEquals("xdoe")), key(username, same("jdoe")), key(username, notSame("xdoe")), key(username, startsWith("jd")), key(username, endsWith("oe")), key(username, contains("do")), key(username, matches("/do/")), key(username, in("jdoe", "xdoe")), key(properties, keyExists("xxx")), key(properties, keyNotExists("yyy")), key(username, same(null)), key(username, notSame(null)), key(username, empty()), key(username, not(empty())), key(logins, greaterThan(1)), key(logins, greaterThanEqual(2)), key(logins, lessThan(3)), key(logins, lessThanEqual(4)))',
            $data
        );
    }

    public function testDeserializeEquals(): void
    {
        $serializer = new StringExpressionSerializer();

        $this->assertEquals(new Equals('jdoe'), $serializer->deserialize('equals("jdoe")'));
        $this->assertEquals(new Equals(99), $serializer->deserialize('equals(99)'));
    }

    public function testDeserializeKey(): void
    {
        $serializer = new StringExpressionSerializer();

        $this->assertEquals(
            new Key('username', new Equals('jdoe')),
            $serializer->deserialize('key(username, equals("jdoe"))')
        );
    }

    public function testDeserializeAnd(): void
    {
        $serializer = new StringExpressionSerializer();

        $this->assertEquals(
            new AndX([
                new Key('username', new Equals('jdoe')),
                new Key('username', new NotEquals('xdoe')),
            ]),
            $serializer->deserialize('and(key(username, equals("jdoe")), key(username, notEquals("xdoe")))')
        );
    }

    public function testDeserializeOr(): void
    {
        $serializer = new StringExpressionSerializer();

        $this->assertEquals(
            new OrX([
                new Key('username', new Equals('jdoe')),
                new Key('username', new NotEquals('xdoe')),
            ]),
            $serializer->deserialize('or(key(username, equals("jdoe")), key(username, notEquals("xdoe")))')
        );
    }

    public function testDeserializeNot(): void
    {
        $serializer = new StringExpressionSerializer();

        $this->assertEquals(
            new Not(new Key('username', new Equals('jdoe'))),
            $serializer->deserialize('not(key(username, equals("jdoe")))')
        );
    }

    public function testDeserializeAndWithNestedExpression(): void
    {
        $serializer = new StringExpressionSerializer();

        $this->assertEquals(
            new OrX([
                new AndX([
                    new Key('username', new Equals('jdoe')),
                    new Key('firstname', new StartsWith('John')),
                ]),
                new Key('lastname', new EndsWith('oe')),
            ]),
            $serializer->deserialize('or(and(key(username, equals("jdoe")), key(firstname, startsWith("John"))), key(lastname, endsWith("oe")))')
        );
    }

    public function testDeserializeComparisons1(): void
    {
        $serializer = new StringExpressionSerializer();

        $data = 'and(key(username, equals("jdoe")), key(username, notEquals("xdoe")), key(username, same("ydoe")), key(username, notSame("zdoe")))';
        $this->assertEquals(
            new AndX(
                [
                    new Key('username', new Equals('jdoe')),
                    new Key('username', new NotEquals('xdoe')),
                    new Key('username', new Same('ydoe')),
                    new Key('username', new NotSame('zdoe')),
                ]
            ),
            $serializer->deserialize($data)
        );
    }

    public function testDeserializeComparisons2(): void
    {
        $serializer = new StringExpressionSerializer();

        $data = 'and(key(firstname, startsWith("Joh")), key(firstname, endsWith("ohn")), key(firstname, contains("oh")), key(firstname, matches("/john/")))';
        $this->assertEquals(
            new AndX(
                [
                    new Key('firstname', new StartsWith('Joh')),
                    new Key('firstname', new EndsWith('ohn')),
                    new Key('firstname', new Contains('oh')),
                    new Key('firstname', new Matches('/john/')),
                ]
            ),
            $serializer->deserialize($data)
        );
    }

    public function testDeserializeComparisons3(): void
    {
        $serializer = new StringExpressionSerializer();

        $data = 'and(key(username, in("jdoe", "xdoe")), key(properties, keyExists("xxx")), key(properties, keyNotExists("yyy")))';
        $this->assertEquals(
            new AndX(
                [
                    new Key('username', new In(['jdoe', 'xdoe'])),
                    new Key('properties', new KeyExists('xxx')),
                    new Key('properties', new KeyNotExists('yyy')),
                ]
            ),
            $serializer->deserialize($data)
        );
    }

    public function testDeserializeComparisons4(): void
    {
        $serializer = new StringExpressionSerializer();

        $data = 'and(key(username, same(null())), key(username, not(same(null()))), key(username, isEmpty()), key(username, not(isEmpty()))';
        $this->assertEquals(
            new AndX([
                new Key('username', new Same(null)),
                new Key('properties', new Not(new Same(null))),
                new Key('properties', new IsEmpty()),
                new Key('properties', new Not(new IsEmpty())),
            ]),
            $serializer->deserialize($data)
        );

        $data = 'and(key(logins, greaterThan(1)), key(logins, greaterThanEquals(2)), key(logins, lessThan(3)), key(logins, lessThanEquals(4)))';
        $this->assertEquals(
            new AndX([
                new Key('logins', new GreaterThan(1)),
                new Key('logins', new GreaterThanEqual(2)),
                new Key('logins', new LessThan(3)),
                new Key('logins', new LessThanEqual(4)),
            ]),
            $serializer->deserialize($data)
        );
    }
}
