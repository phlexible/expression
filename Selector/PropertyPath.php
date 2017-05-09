<?php

/*
 * This file is part of the phlexible package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Component\Expression\Selector;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Webmozart\Expression\Expression;
use Webmozart\Expression\Logic\AndX;
use Webmozart\Expression\Logic\OrX;
use Webmozart\Expression\Selector\Selector;

/**
 * Checks whether an array key/property/method matches an expression.
 *
 * @since  1.0
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class PropertyPath extends Selector
{
    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @var PropertyAccessor
     */
    private $accessor;

    /**
     * Creates the expression.
     *
     * @param string|int $propertyPath The property path.
     * @param Expression $expr         The expression to evaluate for the property path.
     */
    public function __construct($propertyPath, Expression $expr)
    {
        parent::__construct($expr);

        $this->propertyPath = (string) $propertyPath;

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Returns the property path.
     *
     * @return string|int The property path.
     */
    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate($value)
    {
        if (!$this->accessor->isReadable($value, $this->propertyPath)) {
            return false;
        }

        return $this->expr->evaluate($this->accessor->getValue($value, $this->propertyPath));
    }

    /**
     * {@inheritdoc}
     */
    public function equivalentTo(Expression $other)
    {
        if (!parent::equivalentTo($other)) {
            return false;
        }

        /* @var static $other */
        return $this->propertyPath === $other->propertyPath;
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        $exprString = $this->expr->toString();

        if ($this->expr instanceof AndX || $this->expr instanceof OrX) {
            return $this->propertyPath.'{'.$exprString.'}';
        }

        // Append "functions" with "."
        if (isset($exprString[0]) && ctype_alpha($exprString[0])) {
            return $this->propertyPath.'.'.$exprString;
        }

        return $this->propertyPath.$exprString;
    }
}
