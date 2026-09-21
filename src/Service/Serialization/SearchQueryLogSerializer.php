<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\Entity\SearchQueryLogEntity;

final class SearchQueryLogSerializer
{
    /**
     * @param iterable<SearchQueryLogEntity> $logs
     *
     * @return list<array<string, mixed>>
     */
    public function serializeLogs(iterable $logs): array
    {
        $serialized = [];

        foreach ($logs as $log) {
            $serialized[] = $this->serialize($log);
        }

        return $serialized;
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(SearchQueryLogEntity $log): array
    {
        return [
            'id' => $log->getId(),
            'query' => $log->getQueryText(),
            'userId' => $log->getUserId(),
            'vendorId' => $log->getVendorId(),
            'correlationId' => $log->getCorrelationId(),
            'requestId' => $log->getRequestId(),
            'sourceComponent' => $log->getSourceComponent(),
            'sourceOperation' => $log->getSourceOperation(),
            'providerName' => $log->getProviderName(),
            'providerTotal' => $log->getProviderTotal(),
            'returnedTotal' => $log->getReturnedTotal(),
            'deniedCount' => $log->getDeniedCount(),
            'durationMs' => $log->getDurationMs(),
            'successful' => $log->isSuccessful(),
            'errorClass' => $log->getErrorClass(),
            'errorMessage' => $log->getErrorMessage(),
            'metadata' => $log->getMetadata(),
            'createdAt' => $log->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
