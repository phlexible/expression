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
use Webmozart\Expression\Constraint;
use Webmozart\Expression\Expression;
use Webmozart\Expression\Logic;
use Webmozart\Expression\Selector;
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
        if ($expr instanceof Logic\AndX) {
            $this->currentStack->push(new SplStack());
        } elseif ($expr instanceof Logic\OrX) {
            $this->currentStack->push(new SplStack());
        } elseif ($expr instanceof Logic\Not) {
            $this->currentStack->push(new SplStack());
        } elseif ($expr instanceof Selector\Key) {
            if ($this->currentField) {
                throw UnsupportedExpressionException::unsupportedSubKeys();
            }
            $this->currentField = $expr->getKey();
        } elseif ($expr instanceof Selector\Property) {
            if ($this->currentField) {
                throw UnsupportedExpressionException::unsupportedSubProperties();
            }
            $this->currentField = $expr->getPropertyName();
        } elseif ($expr instanceof PropertyPath) {
            if ($this->currentField) {
                throw UnsupportedExpressionException::unsupportedSubProperties();
            }
            $this->currentField = $expr->getPropertyPath();
        } elseif ($expr instanceof Selector\Method ||
            $expr instanceof Selector\All ||
            $expr instanceof Selector\AtLeast ||
            $expr instanceof Selector\AtMost ||
            $expr instanceof Selector\Exactly
        ) {
            throw UnsupportedExpressionException::unsupportedSelector($expr);
        }

        return $expr;
    }

    public function leaveExpression(Expression $expr): Expression
    {
        if ($expr instanceof Logic\AndX) {
            $conjunctionStack = $this->currentStack->pop();
            $current = $this->qb->expr()->andX();
            while ($conjunctionStack->count()) {
                $current->add($conjunctionStack->pop());
            }
            $this->currentStack->top()->push($current);
        } elseif ($expr instanceof Logic\OrX) {
            $disjunctionStack = $this->currentStack->pop();
            $current = $this->qb->expr()->orX();
            while ($disjunctionStack->count()) {
                $current->add($disjunctionStack->pop());
            }
            $this->currentStack->top()->push($current);
        } elseif ($expr instanceof Logic\Not) {
            $negatedStack = $this->currentStack->pop();
            $current = $this->qb->expr()->not($negatedStack->pop());
            $this->currentStack->top()->push($current);
        } elseif ($expr instanceof Selector\Key) {
            $this->currentField = null;
        } else {
            $this->currentStack->top()->push($this->walkConstraint($expr));
        }

        return $expr;
    }

    public function walkConstraint(Expression $expr): Comparison
    {
        if ($expr instanceof Logic\AlwaysTrue) {
            return $this->qb->expr()->eq(1, 1);
        }
        if ($expr instanceof Logic\AlwaysFalse) {
            return $this->qb->expr()->eq(1, 0);
        }

        if (!$this->currentField) {
            throw UnsupportedExpressionException::unsupportedConstraintWithoutField();
        }

        $field = $this->alias.'.'.$this->currentField;

        if ($expr instanceof Constraint\Contains) {
            return $this->qb->expr()->like($field, $this->literal("%{$expr->getComparedValue()}%"));
        }
        if ($expr instanceof Constraint\EndsWith) {
            return $this->qb->expr()->eq($field, $this->literal("%{$expr->getAcceptedSuffix()}"));
        }
        if ($expr instanceof Constraint\Equals) {
            return $this->qb->expr()->eq($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof Constraint\GreaterThan) {
            return $this->qb->expr()->gt($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof Constraint\GreaterThanEqual) {
            return $this->qb->expr()->gte($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof Constraint\In) {
            return $this->qb->expr()->in($field, $this->literal($expr->getAcceptedValues()));
        }
        if ($expr instanceof Constraint\IsEmpty) {
            throw UnsupportedExpressionException::unsupportedConstraint($expr);
        }
        if ($expr instanceof Constraint\IsInstanceOf) {
            throw UnsupportedExpressionException::unsupportedConstraint($expr);
        }
        if ($expr instanceof Constraint\KeyExists) {
            throw UnsupportedExpressionException::unsupportedConstraint($expr);
        }
        if ($expr instanceof Constraint\KeyNotExists) {
            throw UnsupportedExpressionException::unsupportedConstraint($expr);
        }
        if ($expr instanceof Constraint\LessThan) {
            return $this->qb->expr()->lt($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof Constraint\LessThanEqual) {
            return $this->qb->expr()->lte($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof Constraint\Matches) {
            throw UnsupportedExpressionException::unsupportedConstraint($expr);
        }
        if ($expr instanceof Constraint\NotEquals) {
            return $this->qb->expr()->neq($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof Constraint\NotSame) {
            return $this->qb->expr()->neq($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof Constraint\Same) {
            return $this->qb->expr()->eq($field, $this->literal($expr->getComparedValue()));
        }
        if ($expr instanceof Constraint\StartsWith) {
            return $this->qb->expr()->like($field, $this->literal("{$expr->getAcceptedPrefix()}%"));
        }

        throw UnhandledExpressionException::fromConstraint($expr);
    }

    /**
     * @param mixed $value
     */
    private function literal($value): Literal
    {
        if (is_array($value)) {
            foreach ($value as $index => $v) {
                $value[$index] = $this->qb->expr()->literal($v);
            }
        } else {
            $value = $this->qb->expr()->literal($value);
        }

        return $value;
    }
}
