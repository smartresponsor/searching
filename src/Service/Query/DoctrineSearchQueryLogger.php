<?php

declare(strict_types=1);

namespace App\Searching\Service\Query;

use App\Searching\Entity\SearchQueryLogEntity;
use App\Searching\ServiceInterface\Query\SearchQueryLoggerInterface;
use App\Searching\Value\Query\SearchQueryExecutionTrace;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineSearchQueryLogger implements SearchQueryLoggerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private bool $flushImmediately = true,
    ) {
    }

    public function log(SearchQueryExecutionTrace $trace): void
    {
        $this->entityManager->persist(SearchQueryLogEntity::fromTrace($trace));

        if ($this->flushImmediately) {
            $this->entityManager->flush();
        }
    }
}
