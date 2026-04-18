<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

use DualMedia\DtoRequestBundle\Dto\Enum\ActionCondition;

readonly class Action
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        public ActionCondition|\Closure $when,
        public int $statusCode,
        public string|null $message,
        public array $headers,
        public string|null $description = null,
    ) {
    }
}
