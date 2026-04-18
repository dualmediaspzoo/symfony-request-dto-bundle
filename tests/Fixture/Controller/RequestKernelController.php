<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Controller;

use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Request\ScalarActionRequestDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Request\ScalarRequestDto;
use Symfony\Component\HttpFoundation\Response;

class RequestKernelController
{
    public const int OK_STATUS = 219;

    public const int CONTROLLER_ACTION_STATUS = 220;

    public function valid(
        ScalarRequestDto $dto
    ): Response {
        return new Response('name='.$dto->name, self::OK_STATUS);
    }

    public function invalid(
        ScalarRequestDto $dto
    ): Response {
        return new Response('should-not-reach', self::OK_STATUS);
    }

    public function action(
        ScalarActionRequestDto $dto
    ): Response {
        return new Response('controller', self::CONTROLLER_ACTION_STATUS);
    }
}
