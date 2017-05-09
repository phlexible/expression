<?php

/*
 * This file is part of the phlexible package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Component\Tests\Expression\Selector;

use Phlexible\Component\Expression\Selector\PropertyPath;
use PHPUnit\Framework\TestCase;
use Webmozart\Expression\Constraint\EndsWith;
use Webmozart\Expression\Constraint\GreaterThan;
use Webmozart\Expression\Logic\AndX;

class Tester
{
    public $number1 = 11;
    public $number2 = 9;
    private $number3 = 8;

    public function getNumber4()
    {
        return 7;
    }
}

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class PropertyPathTest extends TestCase
{
    public function testEvaluate()
    {
        $expr1 = new PropertyPath('number1', new GreaterThan(10));
        $expr2 = new PropertyPath('number2', new GreaterThan(10));
        $expr3 = new PropertyPath('number3', new GreaterThan(10));
        $expr4 = new PropertyPath('number4', new GreaterThan(10));

        $tester = new Tester();

        $this->assertTrue($expr1->evaluate($tester));
        $this->assertFalse($expr2->evaluate($tester));
        $this->assertFalse($expr3->evaluate($tester));
        $this->assertFalse($expr4->evaluate($tester));
    }

    public function testToString()
    {
        $expr1 = new PropertyPath('name', new GreaterThan(10));
        $expr2 = new PropertyPath('name', new EndsWith('.css'));
        $expr3 = new PropertyPath('name', new AndX(array(
            new GreaterThan(10),
            new EndsWith('.css'),
        )));

        $this->assertSame('name>10', $expr1->toString());
        $this->assertSame('name.endsWith(".css")', $expr2->toString());
        $this->assertSame('name{>10 && endsWith(".css")}', $expr3->toString());
    }
}
