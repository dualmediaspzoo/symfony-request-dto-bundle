<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Controller;

use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi\HeaderParentDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi\SampleRequestDto;
use Symfony\Component\HttpFoundation\JsonResponse;

class DtoFixtureController
{
    public function submit(
        SampleRequestDto $dto
    ): JsonResponse {
        return new JsonResponse(['ok' => $dto->isValid()]);
    }

    public function headers(
        HeaderParentDto $dto
    ): JsonResponse {
        return new JsonResponse(['ok' => $dto->isValid()]);
    }
}
