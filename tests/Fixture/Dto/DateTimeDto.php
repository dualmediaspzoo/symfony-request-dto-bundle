<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Format;
use DualMedia\DtoRequestBundle\Dto\Attribute\Time;
use DualMedia\DtoRequestBundle\Dto\Enum\Time as TimePreset;

class DateTimeDto extends AbstractDto
{
    public \DateTimeInterface|null $dateField = null;

    #[Format('Y-m-d H:i:s')]
    public \DateTimeInterface|null $formattedDate = null;

    #[Format('Y-m-d')]
    #[Time(TimePreset::EndOfDay)]
    public \DateTimeInterface|null $endOfDay = null;

    /**
     * @var list<\DateTimeInterface>
     */
    public array $dateCollection = [];
}
