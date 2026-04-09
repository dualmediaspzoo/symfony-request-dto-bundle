<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider\Interface;

use Doctrine\Common\Collections\Collection;
use DualMedia\DtoRequestBundle\Metadata\Model\AsDoctrineReference;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\Limit;
use DualMedia\DtoRequestBundle\Metadata\Model\Offset;

/**
 * @template T of object
 *
 * Marker class for entity/object providers for dtos.
 *
 * @phpstan-type MetaFindModel = FindBy|Limit|Offset|AsDoctrineReference
 * @phpstan-type FoundReturnType = T|list<T>|Collection<int, T>|null
 * @phpstan-type FindCriteria = array<string, mixed>
 *
 * @phpstan-type ProviderClosure = \Closure(FindCriteria, list<MetaFindModel>): FoundReturnType
 * @phpstan-type CustomProviderClosure = \Closure(ProviderInterface<T>, FindCriteria, list<MetaFindModel>): FoundReturnType
 */
interface ProviderInterface
{
}
