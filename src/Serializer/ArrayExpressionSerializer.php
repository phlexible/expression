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
use Webmozart\Expression\Constraint;
use Webmozart\Expression\Expression;
use Webmozart\Expression\Logic;
use Webmozart\Expression\Selector;

/**
 * Array expression serializer.
 */
class ArrayExpressionSerializer implements ExpressionSerializerInterface
{
    /**
     * @return mixed[]
     */
    public function serialize(Expression $expr): array
    {
        return $this->serializeExpression($expr);
    }

    /**
     * @param mixed[] $expression
     */
    public function deserialize(array $expression): Expression
    {
        return $this->deserializeExpression($expression);
    }

    /**
     * @return mixed[]
     */
    private function serializeExpression(Expression $expr): array
    {
        $class = get_class($expr);

        if ($expr instanceof Logic\AlwaysFalse) {
            $data = ['logic' => 'false'];
        } elseif ($expr instanceof Logic\AlwaysTrue) {
            $data = ['logic' => 'true'];
        } elseif ($expr instanceof Logic\AndX) {
            $data = ['logic' => 'and', 'conjuncts' => []];
            foreach ($expr->getConjuncts() as $conjunct) {
                $data['conjuncts'][] = $this->serializeExpression($conjunct);
            }
        } elseif ($expr instanceof Logic\Not) {
            $data = [
                'logic' => 'not',
                'negatedExpression' => $this->serializeExpression($expr->getNegatedExpression()),
            ];
        } elseif ($expr instanceof Logic\OrX) {
            $data = ['logic' => 'or', 'disjuncts' => []];
            foreach ($expr->getDisjuncts() as $disjunct) {
                $data['disjuncts'][] = $this->serializeExpression($disjunct);
            }
        } elseif ($expr instanceof Selector\All) {
            $data = ['selector' => 'all', 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Selector\AtLeast) {
            $data = ['selector' => 'atLeast', 'count' => 0, 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Selector\AtMost) {
            $data = ['selector' => 'atMost', 'count' => 0, 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Selector\Count) {
            $data = ['selector' => 'count', 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Selector\Exactly) {
            $data = ['selector' => 'exactly', 'count' => 0, 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Selector\Key) {
            $data = ['selector' => 'key', 'key' => $expr->getKey(), 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Selector\Method) {
            $data = ['selector' => 'method', 'methodName' => $expr->getMethodName(), 'arguments' => $expr->getArguments(), 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Selector\Property) {
            $data = ['selector' => 'property', 'propertyName' => $expr->getPropertyName(), 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof PropertyPath) {
            $data = ['selector' => 'propertyPath', 'propertyPath' => $expr->getPropertyPath(), 'expression' => $this->serializeExpression($expr->getExpression())];
        } elseif ($expr instanceof Constraint\Contains) {
            $data = ['constraint' => 'contains', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof Constraint\EndsWith) {
            $data = ['constraint' => 'endsWith', 'value' => $expr->getAcceptedSuffix()];
        } elseif ($expr instanceof Constraint\Equals) {
            $data = ['constraint' => 'equals', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof Constraint\GreaterThan) {
            $data = ['constraint' => 'greaterThan', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof Constraint\GreaterThanEqual) {
            $data = ['constraint' => 'greaterThanEqual', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof Constraint\In) {
            $data = ['constraint' => 'in', 'value' => $expr->getAcceptedValues()];
        } elseif ($expr instanceof Constraint\IsEmpty) {
            $data = ['constraint' => 'isEmpty'];
        } elseif ($expr instanceof Constraint\IsInstanceOf) {
            $data = ['constraint' => 'instanceof', 'value' => $expr->getClassName()];
        } elseif ($expr instanceof Constraint\KeyExists) {
            $data = ['constraint' => 'keyExists', 'value' => $expr->getKey()];
        } elseif ($expr instanceof Constraint\KeyNotExists) {
            $data = ['constraint' => 'keyNotExists', 'value' => $expr->getKey()];
        } elseif ($expr instanceof Constraint\LessThan) {
            $data = ['constraint' => 'lessThan', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof Constraint\LessThanEqual) {
            $data = ['constraint' => 'lessThanEqual', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof Constraint\Matches) {
            $data = ['constraint' => 'matches', 'value' => $expr->getRegularExpression()];
        } elseif ($expr instanceof Constraint\NotEquals) {
            $data = ['constraint' => 'notEquals', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof Constraint\NotSame) {
            $data = ['constraint' => 'notSame', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof Constraint\Same) {
            $data = ['constraint' => 'same', 'value' => $expr->getComparedValue()];
        } elseif ($expr instanceof Constraint\StartsWith) {
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
                return new Logic\Not($this->deserializeExpression($expression['negatedExpression']));

            case 'or':
                return new Logic\OrX($this->deserializeExpressions($expression['disjuncts']));

            case 'and':
                return new Logic\AndX($this->deserializeExpressions($expression['conjuncts']));

            case 'true':
                return new Logic\AlwaysTrue();

            case 'false':
                return new Logic\AlwaysFalse();
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
                return new Selector\All($this->deserializeExpression($expression['expression']));

            case 'atLeast':
                return new Selector\AtLeast($expression['count'], $this->deserializeExpression($expression['expression']));

            case 'atMost':
                return new Selector\AtMost($expression['count'], $this->deserializeExpression($expression['expression']));

            case 'count':
                return new Selector\Count($this->deserializeExpression($expression['expression']));

            case 'exactly':
                return new Selector\Exactly($expression['count'], $this->deserializeExpression($expression['expression']));

            case 'key':
                return new Selector\Key($expression['key'], $this->deserializeExpression($expression['expression']));

            case 'method':
                return new Selector\Method($expression['methodName'], $expression['arguments'], $this->deserializeExpression($expression['expression']));

            case 'property':
                return new Selector\Property($expression['propertyName'], $this->deserializeExpression($expression['expression']));

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
                return new Constraint\Equals($expression['value']);

            case 'notEquals':
                return new Constraint\NotEquals($expression['value']);

            case 'same':
                return new Constraint\Same($expression['value']);

            case 'notSame':
                return new Constraint\NotSame($expression['value']);

            case 'startsWith':
                return new Constraint\StartsWith($expression['value']);

            case 'endsWith':
                return new Constraint\EndsWith($expression['value']);

            case 'contains':
                return new Constraint\Contains($expression['value']);

            case 'matches':
                return new Constraint\Matches($expression['value']);

            case 'in':
                return new Constraint\In($expression['value']);

            case 'keyExists':
                return new Constraint\KeyExists($expression['value']);

            case 'keyNotExists':
                return new Constraint\KeyNotExists($expression['value']);

            case 'null':
                return new Constraint\Same(null);

            case 'notNull':
                return new Constraint\NotSame(null);

            case 'isEmpty':
                return new Constraint\IsEmpty();

            case 'greaterThan':
                return new Constraint\GreaterThan($expression['value']);

            case 'greaterThanEqual':
                return new Constraint\GreaterThanEqual($expression['value']);

            case 'lessThan':
                return new Constraint\LessThan($expression['value']);

            case 'lessThanEqual':
                return new Constraint\LessThanEqual($expression['value']);
        }

        throw UnsupportedSerializedExpressionException::unsupportedConstraint($expression);
    }
}
