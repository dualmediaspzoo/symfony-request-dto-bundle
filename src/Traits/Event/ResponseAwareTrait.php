<?php

namespace DualMedia\DtoRequestBundle\Traits\Event;

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
