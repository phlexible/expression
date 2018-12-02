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

namespace Phlexible\Component\Expression\Traversal;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Literal;
use Doctrine\ORM\QueryBuilder;
use Phlexible\Component\Expression\Exception\UnhandledExpressionException;
use Phlexible\Component\Expression\Exception\UnsupportedExpressionException;
use Phlexible\Component\Expression\Selector\PropertyPath;
use SplStack;
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
use Webmozart\Expression\Selector\Exactly;
use Webmozart\Expression\Selector\Key;
use Webmozart\Expression\Selector\Method;
use Webmozart\Expression\Selector\Property;
use Webmozart\Expression\Traversal\ExpressionTraverser;
use Webmozart\Expression\Traversal\ExpressionVisitor;

/**
 * Expression visitor.
 */
class QueryBuilderExpressionVisitor implements ExpressionVisitor
{
    private $qb;

    private $alias;

    /**
     * @var string
     */
    private $currentField;

    /**
     * @var SplStack
     */
    private $currentStack;

    public function __construct(QueryBuilder $qb, string $alias)
    {
        $this->qb = $qb;
        $this->alias = $alias;
    }

    /**
     * Applies the given expression to a query builder.
     */
    public function apply(Expression $expr): void
    {
        $this->currentStack = new SplStack();
        $this->currentStack->push(new SplStack());

        $traverser = new ExpressionTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse($expr);

        $this->qb->andWhere($this->currentStack->pop()->pop());
    }

    public function enterExpression(Expression $expr): Expression
    {
        if ($expr instanceof AndX) {
            $this->currentStack->push(new SplStack());
        } elseif ($expr instanceof OrX) {
            $this->currentStack->push(new SplStack());
        } elseif ($expr instanceof Not) {
            $this->currentStack->push(new SplStack());
        } elseif ($expr instanceof Key) {
            if ($this->currentField) {
                throw UnsupportedExpressionException::unsupportedSubKeys();
            }
            $this->currentField = $expr->getKey();
        } elseif ($expr instanceof Property) {
            if ($this->currentField) {
                throw UnsupportedExpressionException::unsupportedSubProperties();
            }
            $this->currentField = $expr->getPropertyName();
        } elseif ($expr instanceof PropertyPath) {
            if ($this->currentField) {
                throw UnsupportedExpressionException::unsupportedSubProperties();
            }
            $this->currentField = $expr->getPropertyPath();
        } elseif ($expr instanceof Method ||
            $expr instanceof All ||
            $expr instanceof AtLeast ||
            $expr instanceof AtMost ||
            $expr instanceof Exactly
        ) {
            throw UnsupportedExpressionException::unsupportedSelector($expr);
        }

        return $expr;
    }

    public function leaveExpression(Expression $expr): Expression
    {
        if ($expr instanceof AndX) {
            $conjunctionStack = $this->currentStack->pop();
            $current = $this->qb->expr()->andX();
            while ($conjunctionStack->count()) {
                $current->add($conjunctionStack->pop());
            }
            $this->currentStack->top()->push($current);
        } elseif ($expr instanceof OrX) {
            $disjunctionStack = $this->currentStack->pop();
            $current = $this->qb->expr()->orX();
            while ($disjunctionStack->count()) {
                $current->add($disjunctionStack->pop());
            }
            $this->currentStack->top()->push($current);
        } elseif ($expr instanceof Not) {
            $negatedStack = $this->currentStack->pop();
            $current = $this->qb->expr()->not($negatedStack->pop());
            $this->currentStack->top()->push($current);
        } elseif ($expr instanceof Key) {
            $this->currentField = null;
        } else {
            $this->currentStack->top()->push($this->walkConstraint($expr));
        }

        return $expr;
    }

    public function walkConstraint(Expression $expr): Comparison
    {
        if ($expr instanceof AlwaysTrue) {
            return $this->qb->expr()->eq(1, 1);
        }
        if ($expr instanceof AlwaysFalse) {
            return $this->qb->expr()->eq(1, 0);
        }

        if (!$this->currentField) {
            throw UnsupportedExpressionException::unsupportedConstraintWithoutField();
        }

        $field = $this->alias.'.'.$this->currentField;
        if (strpos($this->currentField, '.') !== false) {
            [$join, $field] = explode('.', $this->currentField);
            $alias = $join;
            $join = $this->alias.'.'.$join;
            $this->qb->join($join, $alias);
            $field = $alias.'.'.$field;
        }

        if ($expr instanceof Contains) {
            return $this->qb->expr()->like($field, $this->literal("%{$expr->getComparedValue()}%"));
        }
        if ($expr instanceof EndsWith) {
            return $this->qb->expr()->eq($field, $this->literal("%{$expr->getAcceptedSuffix()}"));
        }
        if ($expr instanceof Equals) {
            return $this->qb->expr()->eq($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof GreaterThan) {
            return $this->qb->expr()->gt($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof GreaterThanEqual) {
            return $this->qb->expr()->gte($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof In) {
            return $this->qb->expr()->in($field, $this->literal($expr->getAcceptedValues()));
        }
        if ($expr instanceof IsEmpty) {
            throw UnsupportedExpressionException::unsupportedConstraint($expr);
        }
        if ($expr instanceof IsInstanceOf) {
            throw UnsupportedExpressionException::unsupportedConstraint($expr);
        }
        if ($expr instanceof KeyExists) {
            throw UnsupportedExpressionException::unsupportedConstraint($expr);
        }
        if ($expr instanceof KeyNotExists) {
            throw UnsupportedExpressionException::unsupportedConstraint($expr);
        }
        if ($expr instanceof LessThan) {
            return $this->qb->expr()->lt($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof LessThanEqual) {
            return $this->qb->expr()->lte($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof Matches) {
            throw UnsupportedExpressionException::unsupportedConstraint($expr);
        }
        if ($expr instanceof NotEquals) {
            return $this->qb->expr()->neq($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof NotSame) {
            return $this->qb->expr()->neq($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof Same) {
            return $this->qb->expr()->eq($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof StartsWith) {
            return $this->qb->expr()->like($field, $this->literal("{$expr->getAcceptedPrefix()}%"));
        }

        throw UnhandledExpressionException::fromConstraint($expr);
    }

    /**
     * @param mixed $value
     */
    private function literal($value): Literal
    {
        if (\is_array($value)) {
            foreach ($value as $index => $v) {
                $value[$index] = $this->qb->expr()->literal($v);
            }
        } else {
            $value = $this->qb->expr()->literal($value);
        }

        return $value;
    }
}
