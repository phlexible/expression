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

namespace Phlexible\Component\Expression\Form\Type;

use Phlexible\Component\Expression\Form\Transformer\ExpressionTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for representing an expression.
 */
class ExpressionType extends AbstractType
{
    /**
     * @var ExpressionTransformer
     */
    protected $expressionTransformer;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->expressionTransformer = new ExpressionTransformer();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->expressionTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'phlexible_expression';
    }
}
