<?php

declare(strict_types=1);

namespace App\Searching\Repository;

use App\Searching\Contract\Tuning\SearchSynonymWriterInterface;
use App\Searching\Entity\SearchSynonymEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SearchDoctrineSynonymWriter implements SearchSynonymWriterInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): SearchSynonymEntity
    {
        $synonym = SearchSynonymEntity::create(
            sourceTerm: $this->requiredString($payload, 'sourceTerm', 'source_term'),
            targetTerms: $this->stringList($payload['targetTerms'] ?? $payload['target_terms'] ?? []),
            locale: $this->optionalString($payload['locale'] ?? null),
            enabled: $this->bool($payload['enabled'] ?? true),
        );

        $this->entityManager->persist($synonym);
        $this->entityManager->flush();

        return $synonym;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(SearchSynonymEntity $synonym, array $payload): SearchSynonymEntity
    {
        $synonym->update(
            sourceTerm: $this->requiredString($payload, 'sourceTerm', 'source_term', $synonym->getSourceTerm()),
            targetTerms: $this->stringList($payload['targetTerms'] ?? $payload['target_terms'] ?? $synonym->getTargetTerms()),
            locale: $this->optionalString($payload['locale'] ?? $synonym->getLocale()),
            enabled: $this->bool($payload['enabled'] ?? $synonym->isEnabled()),
        );

        $this->entityManager->flush();

        return $synonym;
    }

    public function delete(SearchSynonymEntity $synonym): void
    {
        $this->entityManager->remove($synonym);
        $this->entityManager->flush();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requiredString(array $payload, string $camelKey, string $snakeKey, ?string $fallback = null): string
    {
        $value = $payload[$camelKey] ?? $payload[$snakeKey] ?? $fallback;
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(sprintf('Missing required field "%s".', $camelKey));
        }

        $value = trim((string) $value);
        if ('' === $value) {
            throw new \InvalidArgumentException(sprintf('Missing required field "%s".', $camelKey));
        }

        return $value;
    }

    private function optionalString(mixed $value): ?string
    {
        if (null === $value || !is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return '' === $value ? null : $value;
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (is_string($value)) {
            $value = array_map('trim', explode(',', $value));
        }

        if (!is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $item) {
            if (!is_scalar($item)) {
                continue;
            }

            $item = trim((string) $item);
            if ('' !== $item) {
                $items[] = $item;
            }
        }

        return array_values(array_unique($items));
    }

    private function bool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
    }
}
