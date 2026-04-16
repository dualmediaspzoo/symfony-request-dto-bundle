<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use DualMedia\DtoRequestBundle\Dto\Enum\ActionCondition;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
readonly class Action
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public ActionCondition|\Closure $when,
        public int $statusCode = 404,
        public string|null $message = null,
        public array $headers = [],
    ) {
    }
}
