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

namespace Phlexible\Component\Expression\Serializer;

use Webmozart\Expression\Expression;

/**
 * Expression serializer interface.
 */
interface ExpressionSerializerInterface
{
    /**
     * @return mixed
     */
    public function serialize(Expression $expr);

    /**
     * @param mixed $expression
     */
    public function deserialize($expression): Expression;
}
