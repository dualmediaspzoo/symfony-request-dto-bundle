<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\HttpFoundation\Request;

class BagAccessor
{
    private const string MISSING = "\0__missing__";

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $cache = [];

    public function __construct(
        private readonly Request $request
    ) {
    }

    /**
     * @param list<string> $segments
     */
    public function has(
        BagEnum $bag,
        array $segments
    ): bool {
        return self::MISSING !== $this->walk($bag, $segments);
    }

    /**
     * @param list<string> $segments
     */
    public function get(
        BagEnum $bag,
        array $segments
    ): mixed {
        $value = $this->walk($bag, $segments);

        return self::MISSING === $value ? null : $value;
    }

    /**
     * @param list<string> $segments
     */
    private function walk(
        BagEnum $bag,
        array $segments
    ): mixed {
        $data = $this->load($bag);

        // Headers and cookies are flat namespaces (no nesting), so parent-DTO
        // path segments are meaningless — only the last segment identifies
        // the actual header/cookie. Headers are also case-insensitive per RFC.
        if (BagEnum::Headers === $bag) {
            $segments = [] === $segments ? [] : [strtolower($segments[array_key_last($segments)])];
        } elseif (BagEnum::Cookies === $bag) {
            $segments = [] === $segments ? [] : [$segments[array_key_last($segments)]];
        }

        foreach ($segments as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return self::MISSING;
            }

            $data = $data[$segment];
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function load(
        BagEnum $bag
    ): array {
        if (array_key_exists($bag->value, $this->cache)) {
            return $this->cache[$bag->value];
        }

        $this->cache[$bag->value] = match ($bag) {
            BagEnum::Query => $this->request->query->all(),
            BagEnum::Request => $this->request->request->all(),
            BagEnum::Attributes => $this->request->attributes->all(),
            BagEnum::Cookies => $this->request->cookies->all(),
            BagEnum::Headers => $this->flattenHeaders($this->request->headers->all()),
            BagEnum::Files => $this->request->files->all(),
        };

        return $this->cache[$bag->value];
    }

    /**
     * Symfony's HeaderBag returns every entry as list<string>. Flatten single-valued
     * entries to scalars so scalar DTO properties can read them directly; multi-valued
     * headers stay as lists and flow into collection properties as-is.
     *
     * @param array<string, array<int, string|null>> $headers
     *
     * @return array<string, mixed>
     */
    private function flattenHeaders(
        array $headers
    ): array {
        $out = [];

        foreach ($headers as $name => $values) {
            $out[$name] = 1 === count($values) ? $values[0] : $values;
        }

        return $out;
    }
}
