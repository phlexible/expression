<?php

/*
 * This file is part of the phlexible package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Component\Expression\Tests\Form\Transformer;

use Phlexible\Component\Expression\Form\Transformer\ExpressionTransformer;
use PHPUnit\Framework\TestCase;
use Webmozart\Expression\Expr;
use Webmozart\Expression\Logic\AlwaysTrue;

/**
 * Expression transformer test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ExpressionTransformerTest extends TestCase
{
    public function testTransform()
    {
        $expr = Expr::true();

        $transformer = new ExpressionTransformer();
        $result = $transformer->transform($expr);

        $this->assertSame(array('logic' => 'true'), $result);
    }

    public function testReverseTransform()
    {
        $expr = array('logic' => 'true');

        $transformer = new ExpressionTransformer();
        $result = $transformer->reverseTransform($expr);

        $this->assertInstanceOf(AlwaysTrue::class, $result);
    }
}
