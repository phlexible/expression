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

namespace Phlexible\Component\ExpressionTests\Selector;

use Phlexible\Component\Expression\Selector\PropertyPath;
use Phlexible\Component\ExpressionTests\Fixtures\PropertyPathTester;
use PHPUnit\Framework\TestCase;
use Webmozart\Expression\Constraint\EndsWith;
use Webmozart\Expression\Constraint\GreaterThan;
use Webmozart\Expression\Logic\AndX;

/**
 * @covers \Phlexible\Component\Expression\Form\Type\ExpressionType
 */
class PropertyPathTest extends TestCase
{
    public function testEvaluate(): void
    {
        $expr1 = new PropertyPath('numberOne', new GreaterThan(10));
        $expr2 = new PropertyPath('numberTwo', new GreaterThan(10));
        $expr3 = new PropertyPath('numberThree', new GreaterThan(10));
        $expr4 = new PropertyPath('numberFour', new GreaterThan(10));

        $tester = new PropertyPathTester();

        $this->assertTrue($expr1->evaluate($tester));
        $this->assertFalse($expr2->evaluate($tester));
        $this->assertFalse($expr3->evaluate($tester));
        $this->assertFalse($expr4->evaluate($tester));
    }

    public function testToString(): void
    {
        $expr1 = new PropertyPath('name', new GreaterThan(10));
        $expr2 = new PropertyPath('name', new EndsWith('.css'));
        $expr3 = new PropertyPath('name', new AndX([
            new GreaterThan(10),
            new EndsWith('.css'),
        ]));

        $this->assertSame('name>10', $expr1->toString());
        $this->assertSame('name.endsWith(".css")', $expr2->toString());
        $this->assertSame('name{>10 && endsWith(".css")}', $expr3->toString());
    }
}
