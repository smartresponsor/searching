<?php

declare(strict_types=1);

namespace App\Searching\Entity;

use App\Searching\Value\Query\SearchQueryExecutionTrace;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'search_query_log')]
class SearchQueryLogEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $queryText = '';

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $userId = null;

    #[ORM\Column(name: 'vendor_id', type: 'string', length: 64, nullable: true)]
    private ?string $vendorId = null;

    #[ORM\Column(name: 'correlation_id', type: 'string', length: 64, nullable: true)]
    private ?string $correlationId = null;

    #[ORM\Column(name: 'request_id', type: 'string', length: 64, nullable: true)]
    private ?string $requestId = null;

    #[ORM\Column(name: 'source_component', type: 'string', length: 120, nullable: true)]
    private ?string $sourceComponent = null;

    #[ORM\Column(name: 'source_operation', type: 'string', length: 120, nullable: true)]
    private ?string $sourceOperation = null;

    #[ORM\Column(type: 'string', length: 64)]
    private string $providerName = 'null';

    #[ORM\Column(type: 'integer')]
    private int $providerTotal = 0;

    #[ORM\Column(type: 'integer')]
    private int $returnedTotal = 0;

    #[ORM\Column(type: 'integer')]
    private int $deniedCount = 0;

    #[ORM\Column(type: 'float')]
    private float $durationMs = 0.0;

    #[ORM\Column(type: 'boolean')]
    private bool $successful = true;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $errorClass = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $metadata = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public static function fromTrace(SearchQueryExecutionTrace $trace): self
    {
        $log = new self();
        $log->queryText = $trace->query;
        $log->userId = $trace->userId;
        $log->vendorId = $trace->vendorId;
        $log->correlationId = $trace->executionContext?->correlationId;
        $log->requestId = $trace->executionContext?->requestId;
        $log->sourceComponent = $trace->executionContext?->sourceComponent;
        $log->sourceOperation = $trace->executionContext?->sourceOperation;
        $log->providerName = $trace->providerName;
        $log->providerTotal = $trace->providerTotal;
        $log->returnedTotal = $trace->returnedTotal;
        $log->deniedCount = $trace->deniedCount;
        $log->durationMs = $trace->durationMs;
        $log->successful = $trace->successful;
        $log->errorClass = $trace->errorClass;
        $log->errorMessage = $trace->errorMessage;
        $log->metadata = [
            'providerMetadata' => $trace->providerMetadata,
            'metadata' => $trace->metadata,
            'executionContext' => $trace->executionContext?->toMetadata() ?? [],
        ];
        $log->createdAt = $trace->executedAt;
        $log->updatedAt = $trace->executedAt;

        return $log;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQueryText(): string
    {
        return $this->queryText;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getVendorId(): ?string
    {
        return $this->vendorId;
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

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getProviderTotal(): int
    {
        return $this->providerTotal;
    }

    public function getReturnedTotal(): int
    {
        return $this->returnedTotal;
    }

    public function getDeniedCount(): int
    {
        return $this->deniedCount;
    }

    public function getDurationMs(): float
    {
        return $this->durationMs;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getErrorClass(): ?string
    {
        return $this->errorClass;
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
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
