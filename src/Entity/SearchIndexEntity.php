<?php

declare(strict_types=1);

namespace App\Searching\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'search_index')]
#[ORM\UniqueConstraint(name: 'uniq_search_index_identity', columns: ['provider', 'component', 'resource_type'])]
#[ORM\Index(name: 'idx_search_index_enabled', columns: ['enabled'])]
#[ORM\Index(name: 'idx_search_index_last_indexed_at', columns: ['last_indexed_at'])]
class SearchIndexEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 120)]
    private string $nameEntity;

    #[ORM\Column(type: 'string', length: 80)]
    private string $provider;

    #[ORM\Column(name: 'index_name', type: 'string', length: 191)]
    private string $indexName;

    #[ORM\Column(type: 'string', length: 120)]
    private string $component;

    #[ORM\Column(name: 'resource_type', type: 'string', length: 120)]
    private string $resourceType;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    #[ORM\Column(name: 'lifecycle_status', type: 'string', length: 40)]
    private string $lifecycleStatus = 'registered';

    #[ORM\Column(name: 'last_lifecycle_operation', type: 'string', length: 40, nullable: true)]
    private ?string $lastLifecycleOperation = null;

    #[ORM\Column(name: 'last_lifecycle_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastLifecycleAt = null;

    #[ORM\Column(name: 'last_lifecycle_error', type: 'text', nullable: true)]
    private ?string $lastLifecycleError = null;

    #[ORM\Column(name: 'last_indexed_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastIndexedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $nameEntity, string $provider, string $indexName, string $component, string $resourceType)
    {
        $now = new \DateTimeImmutable();
        $this->nameEntity = $nameEntity;
        $this->provider = $provider;
        $this->indexName = $indexName;
        $this->component = $component;
        $this->resourceType = $resourceType;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->nameEntity;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getIndexName(): string
    {
        return $this->indexName;
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getLifecycleStatus(): string
    {
        return $this->lifecycleStatus;
    }

    public function getLastLifecycleOperation(): ?string
    {
        return $this->lastLifecycleOperation;
    }

    public function getLastLifecycleAt(): ?\DateTimeImmutable
    {
        return $this->lastLifecycleAt;
    }

    public function getLastLifecycleError(): ?string
    {
        return $this->lastLifecycleError;
    }

    public function getLastIndexedAt(): ?\DateTimeImmutable
    {
        return $this->lastIndexedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function rename(string $nameEntity): void
    {
        $this->nameEntity = $nameEntity;
        $this->touch();
    }

    public function updateIndexName(string $indexName): void
    {
        $this->indexName = $indexName;
        $this->touch();
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
        $this->touch();
    }

    public function markIndexed(?\DateTimeImmutable $indexedAt = null): void
    {
        $this->lastIndexedAt = $indexedAt ?? new \DateTimeImmutable();
        $this->touch();
    }

    public function apply(string $nameEntity, string $indexName, bool $enabled): void
    {
        $this->nameEntity = $nameEntity;
        $this->indexName = $indexName;
        $this->enabled = $enabled;
        $this->touch();
    }

    public function markLifecycleResult(string $operation, string $status, ?string $errorMessage = null): void
    {
        $this->lastLifecycleOperation = $operation;
        $this->lifecycleStatus = $status;
        $this->lastLifecycleAt = new \DateTimeImmutable();
        $this->lastLifecycleError = $errorMessage;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
