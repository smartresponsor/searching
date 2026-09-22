<?php

declare(strict_types=1);

namespace App\Searching\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Defines the search indexed resource entity responsibility within the Searching component runtime and its typed boundaries.
 */
#[ORM\Entity]
#[ORM\Table(name: 'search_indexed_resource')]
#[ORM\UniqueConstraint(name: 'uniq_search_indexed_resource_identity', columns: ['component', 'resource_type', 'resource_id'])]
#[ORM\Index(name: 'idx_search_indexed_resource_status', columns: ['status'])]
#[ORM\Index(name: 'idx_search_indexed_resource_source_updated_at', columns: ['source_updated_at'])]
class SearchIndexedResourceEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 120)]
    private string $component;

    #[ORM\Column(name: 'resource_type', type: 'string', length: 120)]
    private string $resourceType;

    #[ORM\Column(name: 'resource_id', type: 'string', length: 191)]
    private string $resourceId;

    #[ORM\Column(name: 'document_hash', type: 'string', length: 64, nullable: true)]
    private ?string $documentHash = null;

    #[ORM\Column(name: 'indexed_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $indexedAt = null;

    #[ORM\Column(name: 'source_updated_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $sourceUpdatedAt = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'pending';

    #[ORM\Column(name: 'error_message', type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $component, string $resourceType, string $resourceId)
    {
        $now = new \DateTimeImmutable();
        $this->component = $component;
        $this->resourceType = $resourceType;
        $this->resourceId = $resourceId;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getDocumentHash(): ?string
    {
        return $this->documentHash;
    }

    public function getIndexedAt(): ?\DateTimeImmutable
    {
        return $this->indexedAt;
    }

    public function getSourceUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->sourceUpdatedAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Executes the mark indexed responsibility defined by the Searching component contract.
     */
    public function markIndexed(string $documentHash, \DateTimeImmutable $sourceUpdatedAt): void
    {
        $now = new \DateTimeImmutable();
        $this->documentHash = $documentHash;
        $this->sourceUpdatedAt = $sourceUpdatedAt;
        $this->indexedAt = $now;
        $this->status = 'indexed';
        $this->errorMessage = null;
        $this->updatedAt = $now;
    }

    /**
     * Executes the mark unchanged responsibility defined by the Searching component contract.
     */
    public function markUnchanged(string $documentHash, \DateTimeImmutable $sourceUpdatedAt): void
    {
        $this->documentHash = $documentHash;
        $this->sourceUpdatedAt = $sourceUpdatedAt;
        $this->status = 'unchanged';
        $this->errorMessage = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Executes the mark failed responsibility defined by the Searching component contract.
     */
    public function markFailed(string $documentHash, \DateTimeImmutable $sourceUpdatedAt, string $errorMessage): void
    {
        $this->documentHash = $documentHash;
        $this->sourceUpdatedAt = $sourceUpdatedAt;
        $this->status = 'failed';
        $this->errorMessage = $errorMessage;
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Executes the mark removed responsibility defined by the Searching component contract.
     */
    public function markRemoved(): void
    {
        $this->status = 'removed';
        $this->errorMessage = null;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
