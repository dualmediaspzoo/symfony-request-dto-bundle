<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;

class SimpleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    public function getId(): int|null
    {
        return $this->id;
    }
}
