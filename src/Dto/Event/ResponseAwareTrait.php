<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Event;

use Symfony\Component\HttpFoundation\Response;

trait ResponseAwareTrait
{
    protected Response|null $response = null;

    public function getResponse(): Response|null
    {
        return $this->response;
    }

    public function setResponse(
        Response|null $response
    ): static {
        $this->response = $response;
        $this->stopPropagation();

        return $this;
    }
}
