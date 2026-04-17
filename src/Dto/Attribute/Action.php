<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use DualMedia\DtoRequestBundle\Dto\Enum\ActionCondition;
use Symfony\Component\HttpFoundation\Response;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
readonly class Action
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public ActionCondition|\Closure $when,
        public int $statusCode = Response::HTTP_NOT_FOUND,
        public string|null $message = null,
        public array $headers = [],
    ) {
    }
}
