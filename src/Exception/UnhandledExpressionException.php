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
 * Unhandled expression exception for expressions.
 */
final class UnhandledExpressionException extends InvalidArgumentException
{
    public static function fromClass(string $class): self
    {
        return new self(sprintf('Unhandled expression %s', $class));
    }

    public static function fromConstraint(Expression $expr): self
    {
        return new self(sprintf('Constraint %s not handled.', get_class($expr)));
    }
}
