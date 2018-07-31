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

namespace Phlexible\Component\ExpressionTests\Form\Type;

use Phlexible\Component\Expression\Form\Type\ExpressionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;
use Webmozart\Expression\Expr;

/**
 * @covers \Phlexible\Component\Expression\Form\Type\ExpressionType
 */
class ExpressionTypeTest extends TypeTestCase
{
    public function testBuildForm(): void
    {
        $formData = [
            'logic' => 'true',
        ];

        $form = $this->factory->create(ExpressionType::class);

        // submit the data to the form directly
        $form->submit($formData);

        $expression = Expr::true();

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expression, $form->getData());
    }

    public function testGetParent(): void
    {
        $type = new ExpressionType();

        $this->assertSame(TextType::class, $type->getParent());
    }

    public function testGetBlockPrefix(): void
    {
        $type = new ExpressionType();

        $this->assertSame('phlexible_expression', $type->getBlockPrefix());
    }
}
