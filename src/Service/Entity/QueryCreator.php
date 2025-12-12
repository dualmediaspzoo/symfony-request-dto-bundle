<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Service\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;

class QueryCreator
{
    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string|Order> $orderBy
     */
    public function buildQuery(
        QueryBuilder $qb,
        string $alias,
        array $criteria = [],
        array $orderBy = [],
        int|null $limit = null,
        int|null $offset = null
    ): QueryBuilder {
        $qb->setMaxResults($limit)
            ->setFirstResult($offset);

        if (!empty($criteria)) {
            $expr = [];
            /** @var ArrayCollection<int, Parameter> $placeholders */
            $placeholders = new ArrayCollection();

            foreach ($criteria as $field => $value) {
                $fieldExpr = ':p_'.$field;
                $check = Comparison::EQ;

                if (is_array($value)) {
                    $check = 'IN';
                    $fieldExpr = '('.$fieldExpr.')';
                } elseif (null === $value) { // special case
                    $expr[] = $alias.'.'.$field.' IS NULL';

                    continue;
                }

                $expr[] = new Comparison($alias.'.'.$field, $check, $fieldExpr);
                $placeholders[] = new Parameter('p_'.$field, $value);
            }

            $qb->andWhere(...$expr)
                ->setParameters($placeholders);
        }

        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $value) {
                $qb->addOrderBy($alias.'.'.$field, match (true) {
                    $value instanceof Order => $value->value,
                    default => $value,
                });
            }
        }

        return $qb;
    }
}

