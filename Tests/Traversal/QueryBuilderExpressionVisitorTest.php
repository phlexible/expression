<?php

/*
 * This file is part of the phlexible package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Component\Expression\Test\Traversal;

use Doctrine\ORM\QueryBuilder;
use Phlexible\Component\Expression\Test\DoctrineTestHelper;
use Phlexible\Component\Expression\Traversal\QueryBuilderExpressionVisitor;
use PHPUnit\Framework\TestCase;
use Webmozart\Expression\Expr;
use Webmozart\Expression\Expression;

/**
 * Message criteria array parser.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class QueryBuilderExpressionVisitorTest extends TestCase
{
    /**
     * @return QueryBuilder
     */
    private function createQueryBuilder()
    {
        $em = DoctrineTestHelper::createTestEntityManager();

        return $em
            ->createQueryBuilder('m')
            ->select('m')
            ->from('PhlexibleTestsDoctrine:Person', 'm');
    }

    /**
     * @param Expression $expr
     *
     * @return QueryBuilder $qb
     */
    private function applyVisitor(Expression $expr)
    {
        $qb = $this->createQueryBuilder();

        $visitor = new QueryBuilderExpressionVisitor($qb, 'm');
        $visitor->apply($expr);

        return $qb;
    }

    /**
     * @group functional
     */
    public function testApplySimpleExpression()
    {
        $expr = Expr::key('channel', Expr::equals('element'));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("m.channel = 'element'", (string) $qb->getDQLPart('where'));
    }

    /**
     * @group functional
     */
    public function testApplyTrueExpression()
    {
        $expr = Expr::true();

        $qb = $this->applyVisitor($expr);

        $this->assertSame('1 = 1', (string) $qb->getDQLPart('where'));
    }

    /**
     * @group functional
     */
    public function testApplySimpleAndExpression()
    {
        $expr = Expr::true()->andKey('createdAt', Expr::greaterThan('2015-01-01 02:03:04'));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("m.createdAt > '2015-01-01 02:03:04'", (string) $qb->getDQLPart('where'));
    }

    /**
     * @group functional
     */
    public function testApplyAndExpression()
    {
        $expr = Expr::key('channel', Expr::equals('element'))
            ->andKey('role', Expr::equals('ROLE_ELEMENT'));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("m.role = 'ROLE_ELEMENT' AND m.channel = 'element'", (string) $qb->getDQLPart('where'));
    }

    /**
     * @group functional
     */
    public function testApplyOrExpression()
    {
        $expr = Expr::key('channel', Expr::equals('element'))
            ->orKey('channel', Expr::equals('user'));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("m.channel = 'user' OR m.channel = 'element'", (string) $qb->getDQLPart('where'));
    }

    /**
     * @group functional
     */
    public function testApplyNotWithJunctionExpression()
    {
        $expr = Expr::not(
            Expr::key('channel', Expr::equals('element'))
                ->orKey('channel', Expr::equals('user'))
        );

        $qb = $this->applyVisitor($expr);

        $this->assertSame("NOT(m.channel = 'user' OR m.channel = 'element')", (string) $qb->getDQLPart('where'));
    }

    /**
     * @group functional
     */
    public function testApplyNotExpression()
    {
        $expr = Expr::key('channel', Expr::equals('element'))
            ->orNot(Expr::key('channel', Expr::equals('user')));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("NOT(m.channel = 'user') OR m.channel = 'element'", (string) $qb->getDQLPart('where'));
    }

    /**
     * @group functional
     */
    public function testApplyWeirdNotExpression()
    {
        $expr = Expr::not(Expr::not(Expr::not(Expr::key('channel', Expr::equals('user')))));

        $qb = $this->applyVisitor($expr);

        $this->assertSame("NOT(NOT(NOT(m.channel = 'user')))", (string) $qb->getDQLPart('where'));
    }

    /**
     * @group functional
     */
    public function testApplyJunctionExpression()
    {
        $expr = Expr::key('role', Expr::equals('ROLE_ELEMENT'))->andX(
            Expr::key('channel', Expr::equals('element'))
                ->orKey('channel', Expr::equals('user'))
        );

        $qb = $this->applyVisitor($expr);

        $this->assertSame("(m.channel = 'user' OR m.channel = 'element') AND m.role = 'ROLE_ELEMENT'", (string) $qb->getDQLPart('where'));
    }

    /**
     * @group functional
     * @expectedException \Phlexible\Component\Expression\Exception\UnsupportedExpressionException
     */
    public function testApplyExpressionWithoutKeyThrowsException()
    {
        $expr = Expr::equals('user');

        $this->applyVisitor($expr);
    }

    /**
     * @group functional
     * @expectedException \Phlexible\Component\Expression\Exception\UnsupportedExpressionException
     */
    public function testApplyUnsupportedComparisonThrowsException()
    {
        $expr = Expr::matches('test', 'user');

        $this->applyVisitor($expr);
    }

    /**
     * @group functional
     * @expectedException \Phlexible\Component\Expression\Exception\UnsupportedExpressionException
     */
    public function testApplyUnsupportedSelectorThrowsException()
    {
        $expr = Expr::all(Expr::key('user', Expr::equals('test')));

        $this->applyVisitor($expr);
    }

    /**
     * @group functional
     * @expectedException \Phlexible\Component\Expression\Exception\UnsupportedExpressionException
     */
    public function testApplyNestedKeysThrowsException()
    {
        $expr = Expr::key('user', Expr::key('property', Expr::equals('test')));

        $this->applyVisitor($expr);
    }
}
