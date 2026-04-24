<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Service;

use DualMedia\DtoRequestBundle\Provider\Interface\ProviderInterface;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\Product;

/**
 * @implements ProviderInterface<Product>
 */
class ProductProvider implements ProviderInterface
{
    /**
     * @var array<string, Product>
     */
    public array $store = [];

    /**
     * @param array<string, mixed> $criteria
     */
    public function findByCode(
        array $criteria
    ): Product|null {
        $code = $criteria['code'] ?? null;

        if (!is_string($code)) {
            return null;
        }

        return $this->store[$code] ?? null;
    }
}
