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

/**
 * Unsupported expression exception for expressions.
 */
final class UnsupportedSerializedExpressionException extends InvalidArgumentException
{
    /**
     * @param mixed[] $data
     */
    public static function unsupportedExpression(array $data): self
    {
        return new self(sprintf('Unsupported expression: %s', json_encode($data)));
    }

    /**
     * @param mixed[] $data
     */
    public static function unsupportedLogic(array $data): self
    {
        return new self(sprintf('Unsupported logic expression: %s', json_encode($data)));
    }

    /**
     * @param mixed[] $data
     */
    public static function unsupportedSelector(array $data): self
    {
        return new self(sprintf('Unsupported selector expression: %s', json_encode($data)));
    }

    /**
     * @param mixed[] $data
     */
    public static function unsupportedConstraint(array $data): self
    {
        return new self(sprintf('Unsupported selector expression: %s', json_encode($data)));
    }
}
