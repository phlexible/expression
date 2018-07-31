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

namespace Phlexible\Component\Expression\Exception;

use Webmozart\Expression\Expression;

/**
 * Unsupported expression exception for expressions.
 */
final class UnsupportedExpressionException extends InvalidArgumentException
{
    public static function unsupportedSubKeys(): self
    {
        return new self('Sub-keys not supported.');
    }

    public static function unsupportedSubProperties(): self
    {
        return new self('Sub-properties not supported.');
    }

    public static function unsupportedSelector(Expression $expr): self
    {
        return new self(sprintf('Selector %s not supported.', get_class($expr)));
    }

    public static function unsupportedConstraintWithoutField(): self
    {
        return new self('Constraint without field not supported.');
    }

    public static function unsupportedConstraint(Expression $expr): self
    {

        return new self(sprintf('Constraint %s not supported.', get_class($expr)));
    }
}
