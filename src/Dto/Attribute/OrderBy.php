<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use Doctrine\Common\Collections\Order;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
readonly class OrderBy
{
    public function __construct(
        public string $field,
        public Order $order = Order::Descending
    ) {
    }
}
