<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY"})
 * @NamedArgumentConstructor()
 */
class Bag implements DtoAnnotationInterface
{
    public const ALLOWED_VALUES = [
        "query",
        "request",
        "attributes",
        "files",
        "cookies",
        "headers",
    ];

    /**
     * Which bag of {@link Request} the path is in
     *
     * @var string
     * @Enum(Bag::ALLOWED_VALUES)
     */
    public string $bag = "request";

    public function __construct(
        string $bag = "request"
    ) {
        if (!in_array($bag, self::ALLOWED_VALUES)) {
            throw new \RuntimeException(sprintf(
                "Invalid value %s, expected one of %s",
                $bag,
                implode(", ", self::ALLOWED_VALUES)
            ));
        }
        $this->bag = $bag;
    }

    public function isHeader(): bool
    {
        return "headers" === $this->bag;
    }
}
