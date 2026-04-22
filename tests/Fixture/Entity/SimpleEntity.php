<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SimpleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int|null $id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private string|null $name = null;

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function setName(
        string|null $name
    ): self {
        $this->name = $name;

        return $this;
    }
}
