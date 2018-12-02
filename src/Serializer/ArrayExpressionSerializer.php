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

use Phlexible\Component\Expression\Exception\UnhandledExpressionException;
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

/**
 * Array expression serializer.
 */
class ArrayExpressionSerializer implements ExpressionSerializerInterface
{
    /**
     * @return mixed[]
     */
    public function serialize(Expression $expr)
    {
        return $this->serializeExpression($expr);
    }

    /**
     * @param mixed[] $expression
     */
    public function deserialize($expression): Expression
    {
        if (!is_array($expression)) {
            throw new \Exception("bla");
        }

        return $this->deserializeExpression($expression);
    }

    /**
     * @return mixed[]
     */
    private function serializeExpression(Expression $expr): array
    {
        $class = get_class($expr);

        if ($expr instanceof AlwaysFalse) {
            $data = ['logic' => 'false'];
        } elseif ($expr instanceof AlwaysTrue) {
            $data = ['logic' => 'true'];
        } elseif ($expr instanceof AndX) {
            $data = ['logic' => 'and', 'conjuncts' => []];
            foreach ($expr->getConjuncts() as $conjunct) {
                $data['conjuncts'][] = $this->serializeExpression($conjunct);
            }
        } elseif ($expr instanceof Not) {
            $data = [
                'logic' => 'not',
                'negatedExpression' => $this->serializeExpression($expr->getNegatedExpression()),
            ];
        } elseif ($expr instanceof OrX) {
            $data = ['logic' => 'or', 'disjuncts' => []];
            foreach ($expr->getDisjuncts() as $disjunct) {
                $data['disjuncts'][] = $this->serializeExpression($disjunct);
            }
        } elseif ($expr instanceof All) {
            $data = ['selector' => 'all', 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof AtLeast) {
            $data = ['selector' => 'atLeast', 'count' => 0, 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof AtMost) {
            $data = ['selector' => 'atMost', 'count' => 0, 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Count) {
            $data = ['selector' => 'count', 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Exactly) {
            $data = ['selector' => 'exactly', 'count' => 0, 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Key) {
            $data = ['selector' => 'key', 'key' => $expr->getKey(), 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Method) {
            $data = ['selector' => 'method', 'methodName' => $expr->getMethodName(), 'arguments' => $expr->getArguments(), 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Property) {
            $data = ['selector' => 'property', 'propertyName' => $expr->getPropertyName(), 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof PropertyPath) {
            $data = ['selector' => 'propertyPath', 'propertyPath' => $expr->getPropertyPath(), 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Contains) {
            $data = ['constraint' => 'contains', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof EndsWith) {
            $data = ['constraint' => 'endsWith', 'value' => $expr->getAcceptedSuffix()];
        } elseif ($expr instanceof Equals) {
            $data = ['constraint' => 'equals', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof GreaterThan) {
            $data = ['constraint' => 'greaterThan', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof GreaterThanEqual) {
            $data = ['constraint' => 'greaterThanEqual', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof In) {
            $data = ['constraint' => 'in', 'value' => $expr->getAcceptedValues()];
        } elseif ($expr instanceof IsEmpty) {
            $data = ['constraint' => 'isEmpty'];
        } elseif ($expr instanceof IsInstanceOf) {
            $data = ['constraint' => 'instanceof', 'value' => $expr->getClassName()];
        } elseif ($expr instanceof KeyExists) {
            $data = ['constraint' => 'keyExists', 'value' => $expr->getKey()];
        } elseif ($expr instanceof KeyNotExists) {
            $data = ['constraint' => 'keyNotExists', 'value' => $expr->getKey()];
        } elseif ($expr instanceof LessThan) {
            $data = ['constraint' => 'lessThan', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof LessThanEqual) {
            $data = ['constraint' => 'lessThanEqual', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof Matches) {
            $data = ['constraint' => 'matches', 'value' => $expr->getRegularExpression()];
        } elseif ($expr instanceof NotEquals) {
            $data = ['constraint' => 'notEquals', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof NotSame) {
            $data = ['constraint' => 'notSame', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof Same) {
            $data = ['constraint' => 'same', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof StartsWith) {
            $data = ['constraint' => 'startsWith', 'value' => $expr->getAcceptedPrefix()];
        } else {
            throw UnhandledExpressionException::fromClass($class);
        }

        return $data;
    }

    /**
     * @param mixed[] $expression
     */
    private function deserializeExpression(array $expression): Expression
    {
        if (isset($expression['logic'])) {
            return $this->deserializeLogic($expression);
        }
        if (isset($expression['selector'])) {
            return $this->deserializeSelector($expression);
        }
        if (isset($expression['constraint'])) {
            return $this->deserializeConstraint($expression);
        }

        throw UnsupportedSerializedExpressionException::unsupportedExpression($expression);
    }

    /**
     * @param mixed[] $expressions
     *
     * @return Expression[]
     */
    private function deserializeExpressions(array $expressions): array
    {
        $data = [];
        foreach ($expressions as $expression) {
            $data[] = $this->deserializeExpression($expression);
        }

        return $data;
    }

    /**
     * @param mixed[] $expression
     */
    private function deserializeLogic(array $expression): Expression
    {
        $logic = $expression['logic'];

        switch ($logic) {
            case 'not':
                return new Not($this->deserializeExpression($expression['negatedExpression']));

            case 'or':
                return new OrX($this->deserializeExpressions($expression['disjuncts']));

            case 'and':
                return new AndX($this->deserializeExpressions($expression['conjuncts']));

            case 'true':
                return new AlwaysTrue();

            case 'false':
                return new AlwaysFalse();
        }

        throw UnsupportedSerializedExpressionException::unsupportedLogic($expression);
    }

    /**
     * @param mixed[] $expression
     */
    private function deserializeSelector(array $expression): Expression
    {
        $selector = $expression['selector'];

        switch ($selector) {
            case 'all':
                return new All($this->deserializeExpression($expression['expression']));

            case 'atLeast':
                return new AtLeast($expression['count'], $this->deserializeExpression($expression['expression']));

            case 'atMost':
                return new AtMost($expression['count'], $this->deserializeExpression($expression['expression']));

            case 'count':
                return new Count($this->deserializeExpression($expression['expression']));

            case 'exactly':
                return new Exactly($expression['count'], $this->deserializeExpression($expression['expression']));

            case 'key':
                return new Key($expression['key'], $this->deserializeExpression($expression['expression']));

            case 'method':
                return new Method($expression['methodName'], $expression['arguments'], $this->deserializeExpression($expression['expression']));

            case 'property':
                return new Property($expression['propertyName'], $this->deserializeExpression($expression['expression']));

            case 'propertyPath':
                return new PropertyPath($expression['propertyPath'], $this->deserializeExpression($expression['expression']));
        }

        throw UnsupportedSerializedExpressionException::unsupportedSelector($expression);
    }

    /**
     * @param mixed[] $expression
     */
    private function deserializeConstraint(array $expression): Expression
    {
        $constraint = $expression['constraint'];

        switch ($constraint) {
            case 'equals':
                return new Equals($expression['value']);

            case 'notEquals':
                return new NotEquals($expression['value']);

            case 'same':
                return new Same($expression['value']);

            case 'notSame':
                return new NotSame($expression['value']);

            case 'startsWith':
                return new StartsWith($expression['value']);

            case 'endsWith':
                return new EndsWith($expression['value']);

            case 'contains':
                return new Contains($expression['value']);

            case 'matches':
                return new Matches($expression['value']);

            case 'in':
                return new In($expression['value']);

            case 'keyExists':
                return new KeyExists($expression['value']);

            case 'keyNotExists':
                return new KeyNotExists($expression['value']);

            case 'null':
                return new Same(null);

            case 'notNull':
                return new NotSame(null);

            case 'isEmpty':
                return new IsEmpty();

            case 'greaterThan':
                return new GreaterThan($expression['value']);

            case 'greaterThanEqual':
                return new GreaterThanEqual($expression['value']);

            case 'lessThan':
                return new LessThan($expression['value']);

            case 'lessThanEqual':
                return new LessThanEqual($expression['value']);
        }

        throw UnsupportedSerializedExpressionException::unsupportedConstraint($expression);
    }
}
