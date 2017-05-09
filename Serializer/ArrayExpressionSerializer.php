<?php

/*
 * This file is part of the phlexible package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Component\Expression\Serializer;

use Phlexible\Component\Expression\Exception\UnhandledExpressionException;
use Phlexible\Component\Expression\Selector\PropertyPath;
use Webmozart\Expression\Constraint;
use Webmozart\Expression\Expression;
use Webmozart\Expression\Logic;
use Webmozart\Expression\Selector;

/**
 * Array expression serializer.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ArrayExpressionSerializer implements ExpressionSerializerInterface
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function serialize(Expression $expr)
    {
        $data = $this->serializeExpression($expr);

        return $data;
    }

    /**
     * {@inheritdoc}
     *
     * @return Expression
     */
    public function deserialize(array $expression)
    {
        return $this->deserializeExpression($expression);
    }

    /**
     * @param Expression $expr
     *
     * @return array
     */
    private function serializeExpression(Expression $expr)
    {
        $class = get_class($expr);

        if ($expr instanceof Logic\AlwaysFalse) {
            $data = array('logic' => 'false');
        } elseif ($expr instanceof Logic\AlwaysTrue) {
            $data = array('logic' => 'true');
        } elseif ($expr instanceof Logic\AndX) {
            $data = array('logic' => 'and', 'conjuncts' => array());
            foreach ($expr->getConjuncts() as $conjunct) {
                $data['conjuncts'][] = $this->serializeExpression($conjunct);
            }
        } elseif ($expr instanceof Logic\Not) {
            $data = array(
                'logic' => 'not',
                'negatedExpression' => $this->serializeExpression($expr->getNegatedExpression()),
            );
        } elseif ($expr instanceof Logic\OrX) {
            $data = array('logic' => 'or', 'disjuncts' => array());
            foreach ($expr->getDisjuncts() as $disjunct) {
                $data['disjuncts'][] = $this->serializeExpression($disjunct);
            }
        } elseif ($expr instanceof Selector\All) {
            $data = array('selector' => 'all', 'expression' => $this->serializeExpression($expr->getExpression()));
        } elseif ($expr instanceof Selector\AtLeast) {
            $data = array('selector' => 'atLeast', 'count' => 0, 'expression' => $this->serializeExpression($expr->getExpression()));
        } elseif ($expr instanceof Selector\AtMost) {
            $data = array('selector' => 'atMost', 'count' => 0, 'expression' => $this->serializeExpression($expr->getExpression()));
        } elseif ($expr instanceof Selector\Count) {
            $data = array('selector' => 'count', 'expression' => $this->serializeExpression($expr->getExpression()));
        } elseif ($expr instanceof Selector\Exactly) {
            $data = array('selector' => 'exactly', 'count' => 0, 'expression' => $this->serializeExpression($expr->getExpression()));
        } elseif ($expr instanceof Selector\Key) {
            $data = array('selector' => 'key', 'key' => $expr->getKey(), 'expression' => $this->serializeExpression($expr->getExpression()));
        } elseif ($expr instanceof Selector\Method) {
            $data = array('selector' => 'method', 'methodName' => $expr->getMethodName(), 'arguments' => $expr->getArguments(), 'expression' => $this->serializeExpression($expr->getExpression()));
        } elseif ($expr instanceof Selector\Property) {
            $data = array('selector' => 'property', 'propertyName' => $expr->getPropertyName(), 'expression' => $this->serializeExpression($expr->getExpression()));
        } elseif ($expr instanceof PropertyPath) {
            $data = array('selector' => 'propertyPath', 'propertyPath' => $expr->getPropertyPath(), 'expression' => $this->serializeExpression($expr->getExpression()));
        } elseif ($expr instanceof Constraint\Contains) {
            $data = array('constraint' => 'contains', 'value' => $expr->getComparedValue());
        } elseif ($expr instanceof Constraint\EndsWith) {
            $data = array('constraint' => 'endsWith', 'value' => $expr->getAcceptedSuffix());
        } elseif ($expr instanceof Constraint\Equals) {
            $data = array('constraint' => 'equals', 'value' => $expr->getComparedValue());
        } elseif ($expr instanceof Constraint\GreaterThan) {
            $data = array('constraint' => 'greaterThan', 'value' => $expr->getComparedValue());
        } elseif ($expr instanceof Constraint\GreaterThanEqual) {
            $data = array('constraint' => 'greaterThanEqual', 'value' => $expr->getComparedValue());
        } elseif ($expr instanceof Constraint\In) {
            $data = array('constraint' => 'in', 'value' => $expr->getAcceptedValues());
        } elseif ($expr instanceof Constraint\IsEmpty) {
            $data = array('constraint' => 'isEmpty');
        } elseif ($expr instanceof Constraint\IsInstanceOf) {
            $data = array('constraint' => 'instanceof', 'value' => $expr->getClassName());
        } elseif ($expr instanceof Constraint\KeyExists) {
            $data = array('constraint' => 'keyExists', 'value' => $expr->getKey());
        } elseif ($expr instanceof Constraint\KeyNotExists) {
            $data = array('constraint' => 'keyNotExists', 'value' => $expr->getKey());
        } elseif ($expr instanceof Constraint\LessThan) {
            $data = array('constraint' => 'lessThan', 'value' => $expr->getComparedValue());
        } elseif ($expr instanceof Constraint\LessThanEqual) {
            $data = array('constraint' => 'lessThanEqual', 'value' => $expr->getComparedValue());
        } elseif ($expr instanceof Constraint\Matches) {
            $data = array('constraint' => 'matches', 'value' => $expr->getRegularExpression());
        } elseif ($expr instanceof Constraint\NotEquals) {
            $data = array('constraint' => 'notEquals', 'value' => $expr->getComparedValue());
        } elseif ($expr instanceof Constraint\NotSame) {
            $data = array('constraint' => 'notSame', 'value' => $expr->getComparedValue());
        } elseif ($expr instanceof Constraint\Same) {
            $data = array('constraint' => 'same', 'value' => $expr->getComparedValue());
        } elseif ($expr instanceof Constraint\StartsWith) {
            $data = array('constraint' => 'startsWith', 'value' => $expr->getAcceptedPrefix());
        } else {
            throw new UnhandledExpressionException("Unhandled expression $class");
        }

        return $data;
    }

    /**
     * @param array $expression
     *
     * @return Expression
     */
    private function deserializeExpression(array $expression)
    {
        if (isset($expression['logic'])) {
            return $this->deserializeLogic($expression);
        } elseif (isset($expression['selector'])) {
            return $this->deserializeSelector($expression);
        } elseif (isset($expression['constraint'])) {
            return $this->deserializeConstraint($expression);
        }

        throw new \InvalidArgumentException('Unsupported expression');
    }

    /**
     * @param array $expressions
     *
     * @return Expression[]
     */
    private function deserializeExpressions(array $expressions)
    {
        $data = array();
        foreach ($expressions as $expression) {
            $data[] = $this->deserializeExpression($expression);
        }

        return $data;
    }

    /**
     * @param array $expression
     *
     * @return Logic\Not
     */
    private function deserializeLogic($expression)
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

        throw new \InvalidArgumentException('Unsupported logic expression');
    }

    /**
     * @param array $expression
     *
     * @return Logic\Not
     */
    private function deserializeSelector($expression)
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

        throw new \InvalidArgumentException('Unsupported selector expression');
    }

    /**
     * @param array $expression
     *
     * @return Expression
     */
    private function deserializeConstraint($expression)
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

        throw new \InvalidArgumentException('Unsupported constraint expression');
    }
}
