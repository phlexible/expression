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

use Phlexible\Component\Expression\Exception\UnsupportedSerializedExpressionException;
use Phlexible\Component\Expression\Selector\PropertyPath;
use Webmozart\Expression\Constraint\Contains;
use Webmozart\Expression\Constraint\EndsWith;
use Webmozart\Expression\Constraint\Equals;
use Webmozart\Expression\Constraint\GreaterThan;
use Webmozart\Expression\Constraint\GreaterThanEqual;
use Webmozart\Expression\Constraint\In;
use Webmozart\Expression\Constraint\IsEmpty;
use Webmozart\Expression\Constraint\IsInstanceOf;
use Webmozart\Expression\Constraint\KeyExists;
use Webmozart\Expression\Constraint\KeyNotExists;
use Webmozart\Expression\Constraint\LessThan;
use Webmozart\Expression\Constraint\LessThanEqual;
use Webmozart\Expression\Constraint\Matches;
use Webmozart\Expression\Constraint\NotEquals;
use Webmozart\Expression\Constraint\NotSame;
use Webmozart\Expression\Constraint\Same;
use Webmozart\Expression\Constraint\StartsWith;
use Webmozart\Expression\Expression;
use Webmozart\Expression\Logic\AlwaysFalse;
use Webmozart\Expression\Logic\AlwaysTrue;
use Webmozart\Expression\Logic\AndX;
use Webmozart\Expression\Logic\Not;
use Webmozart\Expression\Logic\OrX;
use Webmozart\Expression\Selector\All;
use Webmozart\Expression\Selector\AtLeast;
use Webmozart\Expression\Selector\AtMost;
use Webmozart\Expression\Selector\Count;
use Webmozart\Expression\Selector\Exactly;
use Webmozart\Expression\Selector\Key;
use Webmozart\Expression\Selector\Method;
use Webmozart\Expression\Selector\Property;
use Webmozart\Expression\Util\StringUtil;

/**
 * Array expression serializer.
 */
class StringExpressionSerializer implements ExpressionSerializerInterface
{
    /**
     * @return string
     */
    public function serialize(Expression $expr)
    {
        return $this->serializeExpression($expr);
    }

    /**
     * @param string $expression
     */
    public function deserialize($expression): Expression
    {
        if (!is_string($expression)) {
            throw new \Exception("bla");
        }

        return $this->deserializeExpression($expression);
    }

    /**
     * @return mixed[]
     */
    private function serializeExpression(Expression $expr): string
    {
        if ($expr instanceof AlwaysFalse) {
            return 'false()';
        }
        if ($expr instanceof AlwaysTrue) {
            return 'true()';
        }
        if ($expr instanceof Key) {
            return sprintf('key(%s, %s)', $expr->getKey(), $this->serializeExpression($expr->getExpression()));
        }
        if ($expr instanceof AndX) {
            return sprintf('and(%s)', implode(', ', $this->serializeExpressions($expr->getConjuncts())));
        }
        if ($expr instanceof OrX) {
            return sprintf('or(%s)', implode(', ', $this->serializeExpressions($expr->getDisjuncts())));
        }
        if ($expr instanceof Not) {
            return sprintf('not(%s)', $this->serializeValue($expr->getNegatedExpression()));
        }
        if ($expr instanceof Equals) {
            return sprintf('equals(%s)', $this->serializeValue($expr->getComparedValue()));
        }
        if ($expr instanceof NotEquals) {
            return sprintf('notEquals(%s)', $this->serializeValue($expr->getComparedValue()));
        }
        if ($expr instanceof Same) {
            return sprintf('same(%s)', $this->serializeValue($expr->getComparedValue()));
        }
        if ($expr instanceof NotSame) {
            return sprintf('notSame(%s)', $this->serializeValue($expr->getComparedValue()));
        }
        if ($expr instanceof Matches) {
            return sprintf('matches(%s)', $this->serializeValue($expr->getRegularExpression()));
        }
        if ($expr instanceof GreaterThan) {
            return sprintf('greaterThan(%s)', $this->serializeValue($expr->getComparedValue()));
        }
        if ($expr instanceof GreaterThanEqual) {
            return sprintf('greaterThanEqual(%s)', $this->serializeValue($expr->getComparedValue()));
        }
        if ($expr instanceof LessThan) {
            return sprintf('lessThan(%s)', $this->serializeValue($expr->getComparedValue()));
        }
        if ($expr instanceof LessThanEqual) {
            return sprintf('lessThanEqual(%s)', $this->serializeValue($expr->getComparedValue()));
        }

        return (string) $expr;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function serializeValue($value)
    {
        if ($value instanceof Expression) {
            return $this->serializeExpression($value);
        }

        return StringUtil::formatValue($value);
    }

    /**
     * @param Expression[] $expressions
     *
     * @return mixed[]
     */
    private function serializeExpressions(array $expressions): array
    {
        $data = [];

        foreach ($expressions as $expression) {
            $data[] = $this->serializeExpression($expression);
        }

        return $data;
    }

    /**
     * @return Expression[]
     */
    private function deserializeExpressions(string $expressions): array
    {
        $braceLevel = 0;
        $len = strlen($expressions);
        $cur = 0;
        $parts = [];

        for ($i=0; $i<$len; $i++) {
            if ($expressions[$i] === '(') {
                $braceLevel++;
                continue;
            }
            if ($expressions[$i] === ')') {
                $braceLevel--;
                continue;
            }
            if ($expressions[$i] === ',' && $braceLevel === 0) {
                $part = trim(substr($expressions, $cur, $i-$cur));
                $parts[] = $this->deserializeExpression($part);
                $i++;
                $cur = $i;
            }
        }

        $parts[] = $this->deserializeExpression(trim(substr($expressions, $cur, $i)));

        return $parts;
    }

    /**
     * @return mixed
     */
    private function deserializeExpression(string $expression)
    {
        $expression = trim($expression);

        if (preg_match('/^".*"$/', $expression)) {
            return trim(substr($expression, 1, -1));
        }
        if (preg_match('/^\d+$/', $expression)) {
            return (int) $expression;
        }
        if (preg_match('/^[0-9.]+$/', $expression)) {
            return (float) $expression;
        }

        if ($expression === 'true()') {
            return new AlwaysTrue();
        }
        if ($expression === 'false()') {
            return new AlwaysFalse();
        }
        if (strpos($expression, 'not(') === 0) {
            return new Not($this->deserializeExpression(substr($expression, 4, -1)));
        }
        if (strpos($expression, 'or(') === 0) {
            return new OrX($this->deserializeExpressions(substr($expression, 3, -1)));
        }
        if (strpos($expression, 'and(') === 0) {
            return new AndX($this->deserializeExpressions(substr($expression, 4, -1)));
        }

        if (strpos($expression, 'all(') === 0) {
            return new All($this->deserializeExpressions(substr($expression, 4, -1)));
        }
        if (strpos($expression, 'atLeast(') === 0) {
            return new AtLeast($this->deserializeExpressions(substr($expression, 8, -1)));
        }
        if (strpos($expression, 'atMost(') === 0) {
            return new AtMost($this->deserializeExpressions(substr($expression, 8, -1)));
        }
        if (strpos($expression, 'count(') === 0) {
            return new Count($this->deserializeExpressions(substr($expression, 6, -1)));
        }
        if (strpos($expression, 'exactly(') === 0) {
            return new Exactly($this->deserializeExpressions(substr($expression, 8, -1)));
        }
        if (strpos($expression, 'key(') === 0) {
            $parts = explode(', ', substr($expression, 4, -1), 2);

            return new Key($parts[0], $this->deserializeExpression($parts[1]));
        }
        if (strpos($expression, 'method(') === 0) {
            return new Method($this->deserializeExpression(substr($expression, 7, -1)));
        }
        if (strpos($expression, 'property(') === 0) {
            return new Property($this->deserializeExpression(substr($expression, 9, -1)));
        }
        if (strpos($expression, 'propertyPath(') === 0) {
            return new PropertyPath($this->deserializeExpression(substr($expression, 13, -1)));
        }

        if (strpos($expression, 'equals(') === 0) {
            return new Equals($this->deserializeExpression(substr($expression, 7, -1)));
        }
        if (strpos($expression, 'notEquals(') === 0) {
            return new NotEquals($this->deserializeExpression(substr($expression, 10, -1)));
        }
        if (strpos($expression, 'same(') === 0) {
            return new Same($this->deserializeExpression(substr($expression, 5, -1)));
        }
        if (strpos($expression, 'notSame(') === 0) {
            return new NotSame($this->deserializeExpression(substr($expression, 8, -1)));
        }
        if (strpos($expression, 'startsWith(') === 0) {
            return new StartsWith($this->deserializeExpression(substr($expression, 11, -1)));
        }
        if (strpos($expression, 'endsWith(') === 0) {
            return new EndsWith($this->deserializeExpression(substr($expression, 9, -1)));
        }
        if (strpos($expression, 'contains(') === 0) {
            return new Contains($this->deserializeExpression(substr($expression, 9, -1)));
        }
        if (strpos($expression, 'matches(') === 0) {
            return new Matches($this->deserializeExpression(substr($expression, 8, -1)));
        }
        if (strpos($expression, 'in(') === 0) {
            return new In($this->deserializeExpressions(substr($expression, 3, -1)));
        }
        if (strpos($expression, 'instanceOf(') === 0) {
            return new IsInstanceOf($this->deserializeExpressions(substr($expression, 11, -1)));
        }
        if (strpos($expression, 'keyExists(') === 0) {
            return new KeyExists($this->deserializeExpression(substr($expression, 10, -1)));
        }
        if (strpos($expression, 'keyNotExists(') === 0) {
            return new KeyNotExists($this->deserializeExpression(substr($expression, 13, -1)));
        }
        if ($expression === 'empty()') {
            return new IsEmpty();
        }
        if (strpos($expression, 'greaterThan(') === 0) {
            return new GreaterThan($this->deserializeExpression(substr($expression, 12, -1)));
        }
        if (strpos($expression, 'greaterThanEqual(') === 0) {
            return new GreaterThanEqual($this->deserializeExpression(substr($expression, 17, -1)));
        }
        if (strpos($expression, 'lessThan(') === 0) {
            return new LessThan($this->deserializeExpression(substr($expression, 9, -1)));
        }
        if (strpos($expression, 'lessThanEqual(') === 0) {
            return new LessThanEqual($this->deserializeExpression(substr($expression, 14, -1)));
        }

        throw new UnsupportedSerializedExpressionException('bla: __'.$expression.'__');
    }
}
