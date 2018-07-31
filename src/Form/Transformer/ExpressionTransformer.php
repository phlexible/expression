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

namespace Phlexible\Component\Expression\Form\Transformer;

use Phlexible\Component\Expression\Serializer\ArrayExpressionSerializer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Webmozart\Expression\Expression;

// phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint

/**
 * Expression transformer.
 */
class ExpressionTransformer implements DataTransformerInterface
{
    /**
     * @var ArrayExpressionSerializer
     */
    private $serializer;

    public function __construct()
    {
        $this->serializer = new ArrayExpressionSerializer();
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param mixed $expression
     *
     * @return mixed
     */
    public function transform($expression)
    {
        if (!$expression instanceof Expression) {
            return '';
        }

        return $this->serializer->serialize($expression);
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @para mixed $expression
     *
     * @return mixed
     */
    public function reverseTransform($expression)
    {
        if (!$expression) {
            return null;
        }

        if (!is_array($expression)) {
            return null;
        }

        $expression = $this->serializer->deserialize($expression);

        if (null === $expression) {
            throw new TransformationFailedException(sprintf(
                'An issue with number "%s" does not exist!',
                $expression
            ));
        }

        return $expression;
    }
}
