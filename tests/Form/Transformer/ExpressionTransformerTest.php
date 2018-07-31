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

namespace Phlexible\Component\ExpressionTests\Form\Transformer;

use Phlexible\Component\Expression\Form\Transformer\ExpressionTransformer;
use PHPUnit\Framework\TestCase;
use Webmozart\Expression\Expr;
use Webmozart\Expression\Logic\AlwaysTrue;

/**
 * @covers \Phlexible\Component\Expression\Form\Transformer\ExpressionTransformer
 */
class ExpressionTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $expr = Expr::true();

        $transformer = new ExpressionTransformer();
        $result = $transformer->transform($expr);

        $this->assertSame(['logic' => 'true'], $result);
    }

    public function testReverseTransform(): void
    {
        $expr = ['logic' => 'true'];

        $transformer = new ExpressionTransformer();
        $result = $transformer->reverseTransform($expr);

        $this->assertInstanceOf(AlwaysTrue::class, $result);
    }
}
