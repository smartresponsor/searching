<?php

declare(strict_types=1);

namespace App\Searching\Repository;

use App\Searching\Contract\Tuning\SearchRelevanceProfileWriterInterface;
use App\Searching\Entity\SearchRelevanceProfileEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SearchDoctrineRelevanceProfileWriter implements SearchRelevanceProfileWriterInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): SearchRelevanceProfileEntity
    {
        $profile = SearchRelevanceProfileEntity::create(
            nameEntity: $this->requiredString($payload, 'nameEntity'),
            fieldWeights: $this->weights($payload['fieldWeights'] ?? $payload['field_weights'] ?? []),
            component: $this->optionalString($payload['component'] ?? null),
            resourceType: $this->optionalString($payload['resourceType'] ?? $payload['resource_type'] ?? null),
            enabled: $this->bool($payload['enabled'] ?? true),
        );

        $this->entityManager->persist($profile);
        $this->entityManager->flush();

        return $profile;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(SearchRelevanceProfileEntity $profile, array $payload): SearchRelevanceProfileEntity
    {
        $profile->update(
            nameEntity: $this->requiredString($payload, 'nameEntity', $profile->getName()),
            fieldWeights: $this->weights($payload['fieldWeights'] ?? $payload['field_weights'] ?? $profile->getFieldWeights()),
            component: $this->optionalString($payload['component'] ?? $profile->getComponent()),
            resourceType: $this->optionalString($payload['resourceType'] ?? $payload['resource_type'] ?? $profile->getResourceType()),
            enabled: $this->bool($payload['enabled'] ?? $profile->isEnabled()),
        );

        $this->entityManager->flush();

        return $profile;
    }

    public function delete(SearchRelevanceProfileEntity $profile): void
    {
        $this->entityManager->remove($profile);
        $this->entityManager->flush();
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requiredString(array $payload, string $key, ?string $fallback = null): string
    {
        $value = $payload[$key] ?? $fallback;
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException(sprintf('Missing required field "%s".', $key));
        }

        $value = trim((string) $value);
        if ('' === $value) {
            throw new \InvalidArgumentException(sprintf('Missing required field "%s".', $key));
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
     * @return array<string, int|float>
     */
    private function weights(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $weights = [];
        foreach ($value as $field => $weight) {
            if (!is_int($weight) && !is_float($weight) && !is_numeric($weight)) {
                continue;
            }

            $field = trim((string) $field);
            if ('' === $field) {
                continue;
            }

            $numericWeight = $weight + 0;
            $weights[$field] = is_float($numericWeight) ? $numericWeight : (int) $numericWeight;
        }

        return $weights;
    }

    private function bool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
    }
}
