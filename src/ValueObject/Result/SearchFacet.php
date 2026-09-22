<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Result;

/**
 * Defines the search facet responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchFacet
{
    public string $identifier;

    public string $nameEntity;

    /** @var array<string, int> */
    public array $buckets;

    /**
     * @param array<string, int> $buckets
     */
    public function __construct(
        string $nameEntity,
        array $buckets,
    ) {
        $this->identifier = self::normalizeIdentifier($nameEntity);
        $this->nameEntity = $this->identifier;
        $this->buckets = self::normalizeBuckets($buckets);
    }

    private static function normalizeIdentifier(string $identifier): string
    {
        $normalized = mb_strtolower(trim($identifier));

        if ('' === $normalized || !preg_match('/^[a-z0-9_\\-]+$/', $normalized)) {
            throw new \InvalidArgumentException('Facet identifier must use lowercase letters, numbers, dash, or underscore.');
        }

        return $normalized;
    }

    /**
     * @param array<string, int> $buckets
     *
     * @return array<string, int>
     */
    private static function normalizeBuckets(array $buckets): array
    {
        $normalized = [];

        foreach ($buckets as $identifier => $count) {
            $valueIdentifier = mb_strtolower(trim((string) $identifier));
            if ('' === $valueIdentifier || mb_strlen($valueIdentifier) > 128 || !preg_match('/^[a-z0-9][a-z0-9_.:\\-]*$/', $valueIdentifier)) {
                throw new \InvalidArgumentException('Facet value identifier uses an unsupported format.');
            }

            if ($count < 0) {
                throw new \InvalidArgumentException('Facet bucket count must be non-negative.');
            }

            $normalized[$valueIdentifier] = $count;
        }

        uksort($normalized, static function (string $left, string $right) use ($normalized): int {
            $countComparison = $normalized[$right] <=> $normalized[$left];

            return 0 !== $countComparison ? $countComparison : strcmp($left, $right);
        });

        return $normalized;
    }
}
