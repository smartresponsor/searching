<?php

declare(strict_types=1);

namespace App\Searching\Repository;

use App\Searching\Contract\Query\SearchQueryLoggerInterface;
use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\ValueObject\Query\SearchQueryExecutionTrace;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Defines the search doctrine query logger responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchDoctrineQueryLogger implements SearchQueryLoggerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private bool $flushImmediately = true,
    ) {
    }

    /**
     * Executes the log responsibility defined by the Searching component contract.
     */
    public function log(SearchQueryExecutionTrace $trace): void
    {
        $this->entityManager->persist(SearchQueryLogEntity::fromTrace($trace));

        if ($this->flushImmediately) {
            $this->entityManager->flush();
        }
    }
}
