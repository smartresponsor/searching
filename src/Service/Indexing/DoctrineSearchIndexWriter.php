<?php

declare(strict_types=1);

namespace App\Searching\Service\Indexing;

use App\Searching\Entity\SearchIndexEntity;
use App\Searching\Repository\SearchIndexRepository;
use App\Searching\ServiceInterface\Indexing\SearchIndexWriterInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineSearchIndexWriter implements SearchIndexWriterInterface
{
    public function __construct(
        private SearchIndexRepository $repository,
        private EntityManagerInterface $entityManager,
        private bool $flushImmediately = true,
    ) {
    }

    /**
     * @param array<string, mixed> $input
     */
    public function upsert(array $input): SearchIndexEntity
    {
        $provider = $this->requiredString($input, 'provider');
        $component = $this->requiredString($input, 'component');
        $resourceType = $this->requiredString($input, 'resourceType', 'resource_type', 'resource');
        $indexName = $this->requiredString($input, 'indexName', 'index_name');
        $nameEntity = $this->optionalString($input, 'nameEntity') ?? sprintf('%s %s %s', $provider, $component, $resourceType);
        $enabled = $this->optionalBool($input, 'enabled') ?? true;

        $index = $this->repository->getOrCreate($provider, $component, $resourceType, $nameEntity, $indexName);
        $index->apply($nameEntity, $indexName, $enabled);

        $operation = $this->optionalString($input, 'lifecycleOperation', 'lifecycle_operation');
        $status = $this->optionalString($input, 'lifecycleStatus', 'lifecycle_status');
        if (null !== $operation && null !== $status) {
            $index->markLifecycleResult(
                operation: $operation,
                status: $status,
                errorMessage: $this->optionalString($input, 'lifecycleError', 'lifecycle_error'),
            );
        }

        $this->entityManager->persist($index);
        $this->flushIfNeeded();

        return $index;
    }

    /**
     * @param array<string, mixed> $input
     */
    public function update(SearchIndexEntity $index, array $input): SearchIndexEntity
    {
        $nameEntity = $this->optionalString($input, 'nameEntity');
        if (null !== $nameEntity) {
            $index->rename($nameEntity);
        }

        $indexName = $this->optionalString($input, 'indexName', 'index_name');
        if (null !== $indexName) {
            $index->updateIndexName($indexName);
        }

        $enabled = $this->optionalBool($input, 'enabled');
        if (null !== $enabled) {
            $index->setEnabled($enabled);
        }

        if (($input['markIndexed'] ?? $input['mark_indexed'] ?? false) === true) {
            $index->markIndexed();
        }

        $operation = $this->optionalString($input, 'lifecycleOperation', 'lifecycle_operation');
        $status = $this->optionalString($input, 'lifecycleStatus', 'lifecycle_status');
        if (null !== $operation && null !== $status) {
            $index->markLifecycleResult(
                operation: $operation,
                status: $status,
                errorMessage: $this->optionalString($input, 'lifecycleError', 'lifecycle_error'),
            );
        }

        $this->entityManager->persist($index);
        $this->flushIfNeeded();

        return $index;
    }

    public function delete(SearchIndexEntity $index): void
    {
        $this->entityManager->remove($index);
        $this->flushIfNeeded();
    }

    private function flushIfNeeded(): void
    {
        if ($this->flushImmediately) {
            $this->entityManager->flush();
        }
    }

    private function requiredString(array $input, string ...$keys): string
    {
        $value = $this->optionalString($input, ...$keys);
        if (null === $value) {
            throw new \InvalidArgumentException(sprintf('Missing required search index field: %s.', implode(' / ', $keys)));
        }

        return $value;
    }

    private function optionalString(array $input, string ...$keys): ?string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $input)) {
                continue;
            }

            $string = trim((string) $input[$key]);
            if ('' !== $string) {
                return $string;
            }
        }

        return null;
    }

    private function optionalBool(array $input, string $key): ?bool
    {
        if (!array_key_exists($key, $input) || '' === $input[$key]) {
            return null;
        }

        if (is_bool($input[$key])) {
            return $input[$key];
        }

        return filter_var($input[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
