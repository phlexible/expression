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

namespace Phlexible\Component\ExpressionTests\Traversal;

use Doctrine\ORM\QueryBuilder;
use Phlexible\Component\Expression\Exception\UnsupportedExpressionException;
use Phlexible\Component\Expression\Test\DoctrineTestHelper;
use Phlexible\Component\Expression\Traversal\QueryBuilderExpressionVisitor;
use PHPUnit\Framework\TestCase;
use Webmozart\Expression\Expr;
use Webmozart\Expression\Expression;

/**
 * @covers \Phlexible\Component\Expression\Traversal\QueryBuilderExpressionVisitor
 */
class QueryBuilderExpressionVisitorTest extends TestCase
{
    public function testApplySimpleExpression(): void
    {
        $expr = Expr::key('channel', Expr::equals('element'));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("m.channel = 'element'", (string) $qb->getDQLPart('where'));
    }

    public function testApplyTrueExpression(): void
    {
        $expr = Expr::true();

        $qb = $this->applyVisitor($expr);

        $this->assertSame('1 = 1', (string) $qb->getDQLPart('where'));
    }

    public function testApplySimpleAndExpression(): void
    {
        $expr = Expr::true()->andKey('createdAt', Expr::greaterThan('2015-01-01 02:03:04'));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("m.createdAt > '2015-01-01 02:03:04'", (string) $qb->getDQLPart('where'));
    }

    public function testApplyAndExpression(): void
    {
        $expr = Expr::key('channel', Expr::equals('element'))
            ->andKey('role', Expr::equals('ROLE_ELEMENT'));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("m.role = 'ROLE_ELEMENT' AND m.channel = 'element'", (string) $qb->getDQLPart('where'));
    }

    public function testApplyOrExpression(): void
    {
        $expr = Expr::key('channel', Expr::equals('element'))
            ->orKey('channel', Expr::equals('user'));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("m.channel = 'user' OR m.channel = 'element'", (string) $qb->getDQLPart('where'));
    }

    public function testApplyNotWithJunctionExpression(): void
    {
        $expr = Expr::not(
            Expr::key('channel', Expr::equals('element'))
                ->orKey('channel', Expr::equals('user'))
        );

        $qb = $this->applyVisitor($expr);

        $this->assertSame("NOT(m.channel = 'user' OR m.channel = 'element')", (string) $qb->getDQLPart('where'));
    }

    public function testApplyNotExpression(): void
    {
        $expr = Expr::key('channel', Expr::equals('element'))
            ->orNot(Expr::key('channel', Expr::equals('user')));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("NOT(m.channel = 'user') OR m.channel = 'element'", (string) $qb->getDQLPart('where'));
    }

    public function testApplyWeirdNotExpression(): void
    {
        $expr = Expr::not(Expr::not(Expr::not(Expr::key('channel', Expr::equals('user')))));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("NOT(NOT(NOT(m.channel = 'user')))", (string) $qb->getDQLPart('where'));
    }

    public function testApplyJunctionExpression(): void
    {
        $expr = Expr::key('role', Expr::equals('ROLE_ELEMENT'))->andX(
            Expr::key('channel', Expr::equals('element'))
                ->orKey('channel', Expr::equals('user'))
        );

        $qb = $this->applyVisitor($expr);

        $this->assertSame("(m.channel = 'user' OR m.channel = 'element') AND m.role = 'ROLE_ELEMENT'", (string) $qb->getDQLPart('where'));
    }

    public function testApplyExpressionWithoutKeyThrowsException(): void
    {
        $this->expectException(UnsupportedExpressionException::class);

        $expr = Expr::equals('user');

        $this->applyVisitor($expr);
    }

    public function testApplyUnsupportedComparisonThrowsException(): void
    {
        $this->expectException(UnsupportedExpressionException::class);

        $expr = Expr::matches('test', 'user');

        $this->applyVisitor($expr);
    }

    public function testApplyUnsupportedSelectorThrowsException(): void
    {
        $this->expectException(UnsupportedExpressionException::class);

        $expr = Expr::all(Expr::key('user', Expr::equals('test')));

        $this->applyVisitor($expr);
    }

    public function testApplyNestedKeysThrowsException(): void
    {
        $this->expectException(UnsupportedExpressionException::class);

        $expr = Expr::key('user', Expr::key('property', Expr::equals('test')));

        $this->applyVisitor($expr);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $em = DoctrineTestHelper::createTestEntityManager();

        return $em
            ->createQueryBuilder('m')
            ->select('m')
            ->from('PhlexibleTestsDoctrine:Person', 'm');
    }

    private function applyVisitor(Expression $expr): QueryBuilder
    {
        $qb = $this->createQueryBuilder();

        $visitor = new QueryBuilderExpressionVisitor($qb, 'm');
        $visitor->apply($expr);

        return $qb;
    }
}
