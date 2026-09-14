<?php

declare(strict_types=1);

namespace App\Searching\Entity;

use App\Searching\Value\Observability\SearchExecutionContext;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'search_reindex_job')]
#[ORM\Index(name: 'idx_search_reindex_job_status', columns: ['status'])]
#[ORM\Index(name: 'idx_search_reindex_job_component_resource', columns: ['component', 'resource_type'])]
#[ORM\Index(name: 'idx_search_reindex_job_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx_search_reindex_job_idempotency', columns: ['idempotency_key'])]
class SearchReindexJobEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'job_key', type: 'string', length: 64, unique: true)]
    private string $jobKey;

    #[ORM\Column(type: 'string', length: 120, nullable: true)]
    private ?string $component = null;

    #[ORM\Column(name: 'resource_type', type: 'string', length: 120, nullable: true)]
    private ?string $resourceType = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'requested';

    #[ORM\Column(name: 'requested_by', type: 'string', length: 191, nullable: true)]
    private ?string $requestedBy = null;

    #[ORM\Column(name: 'changed_since', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $changedSince = null;

    #[ORM\Column(name: 'idempotency_key', type: 'string', length: 64, nullable: true)]
    private ?string $idempotencyKey = null;

    #[ORM\Column(name: 'dispatch_mode', type: 'string', length: 32, nullable: true)]
    private ?string $dispatchMode = null;

    #[ORM\Column(name: 'correlation_id', type: 'string', length: 64, nullable: true)]
    private ?string $correlationId = null;

    #[ORM\Column(name: 'request_id', type: 'string', length: 64, nullable: true)]
    private ?string $requestId = null;

    #[ORM\Column(name: 'source_component', type: 'string', length: 120, nullable: true)]
    private ?string $sourceComponent = null;

    #[ORM\Column(name: 'source_operation', type: 'string', length: 120, nullable: true)]
    private ?string $sourceOperation = null;

    /** @var array<string, mixed> */
    #[ORM\Column(name: 'execution_context', type: 'json')]
    private array $executionContext = [];

    #[ORM\Column(name: 'queued_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $queuedAt = null;

    #[ORM\Column(name: 'message_attempts', type: 'integer')]
    private int $messageAttempts = 0;

    #[ORM\Column(name: 'last_message_failure_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastMessageFailureAt = null;

    #[ORM\Column(name: 'started_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(name: 'finished_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $finishedAt = null;

    #[ORM\Column(name: 'provider_count', type: 'integer')]
    private int $providerCount = 0;

    #[ORM\Column(name: 'processed_count', type: 'integer')]
    private int $processedCount = 0;

    #[ORM\Column(name: 'failed_count', type: 'integer')]
    private int $failedCount = 0;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: 'json')]
    private array $errors = [];

    #[ORM\Column(name: 'error_message', type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $jobKey, ?string $component = null, ?string $resourceType = null, ?string $requestedBy = null, ?\DateTimeImmutable $changedSince = null, ?string $idempotencyKey = null, ?string $dispatchMode = null, ?SearchExecutionContext $executionContext = null)
    {
        $now = new \DateTimeImmutable();
        $this->jobKey = $jobKey;
        $this->component = $component;
        $this->resourceType = $resourceType;
        $this->requestedBy = $requestedBy;
        $this->changedSince = $changedSince;
        $this->idempotencyKey = $idempotencyKey;
        $this->dispatchMode = $dispatchMode;
        if (null !== $executionContext) {
            $this->correlationId = $executionContext->correlationId;
            $this->requestId = $executionContext->requestId;
            $this->sourceComponent = $executionContext->sourceComponent;
            $this->sourceOperation = $executionContext->sourceOperation;
            $this->executionContext = $executionContext->toMetadata();
        }
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobKey(): string
    {
        return $this->jobKey;
    }

    public function getComponent(): ?string
    {
        return $this->component;
    }

    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getRequestedBy(): ?string
    {
        return $this->requestedBy;
    }

    public function getChangedSince(): ?\DateTimeImmutable
    {
        return $this->changedSince;
    }

    public function getIdempotencyKey(): ?string
    {
        return $this->idempotencyKey;
    }

    public function getDispatchMode(): ?string
    {
        return $this->dispatchMode;
    }

    public function getCorrelationId(): ?string
    {
        return $this->correlationId;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function getSourceComponent(): ?string
    {
        return $this->sourceComponent;
    }

    public function getSourceOperation(): ?string
    {
        return $this->sourceOperation;
    }

    /**
     * @return array<string, mixed>
     */
    public function getExecutionContext(): array
    {
        return $this->executionContext;
    }

    public function getQueuedAt(): ?\DateTimeImmutable
    {
        return $this->queuedAt;
    }

    public function getMessageAttempts(): int
    {
        return $this->messageAttempts;
    }

    public function getLastMessageFailureAt(): ?\DateTimeImmutable
    {
        return $this->lastMessageFailureAt;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function getProviderCount(): int
    {
        return $this->providerCount;
    }

    public function getProcessedCount(): int
    {
        return $this->processedCount;
    }

    public function getFailedCount(): int
    {
        return $this->failedCount;
    }

    /**
     * @return list<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
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

    public function markQueued(string $dispatchMode, string $idempotencyKey): void
    {
        $now = new \DateTimeImmutable();
        $this->status = 'queued';
        $this->dispatchMode = $dispatchMode;
        $this->idempotencyKey = $idempotencyKey;
        $this->queuedAt ??= $now;
        $this->updatedAt = $now;
    }

    public function markDispatchFailed(string $errorMessage): void
    {
        $now = new \DateTimeImmutable();
        ++$this->messageAttempts;
        $this->lastMessageFailureAt = $now;
        $this->errorMessage = $errorMessage;
        $this->errors = [$errorMessage];
        $this->status = 'failed';
        $this->finishedAt = $now;
        $this->updatedAt = $now;
    }

    public function markRunning(int $providerCount): void
    {
        $now = new \DateTimeImmutable();
        $this->status = 'running';
        ++$this->messageAttempts;
        $this->providerCount = $providerCount;
        $this->startedAt ??= $now;
        $this->updatedAt = $now;
    }

    /**
     * @param list<string> $errors
     */
    public function markCompleted(int $providerCount, int $processedCount, int $failedCount, array $errors = []): void
    {
        $now = new \DateTimeImmutable();
        $this->providerCount = $providerCount;
        $this->processedCount = $processedCount;
        $this->failedCount = $failedCount;
        $this->errors = $errors;
        $this->errorMessage = [] === $errors ? null : implode("\n", $errors);
        $this->status = 0 === $failedCount ? 'completed' : 'completed_with_errors';
        $this->finishedAt = $now;
        $this->updatedAt = $now;
    }

    /**
     * @param list<string> $errors
     */
    public function markFailed(int $providerCount, int $processedCount, int $failedCount, array $errors): void
    {
        $now = new \DateTimeImmutable();
        $this->providerCount = $providerCount;
        $this->processedCount = $processedCount;
        $this->failedCount = max(1, $failedCount);
        $this->errors = $errors;
        $this->errorMessage = [] === $errors ? 'Reindex failed.' : implode("\n", $errors);
        $this->status = 'failed';
        $this->finishedAt = $now;
        $this->updatedAt = $now;
    }
}
