<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\Entity\SearchSynonymEntity;

final class SearchSynonymSerializer
{
    /**
     * @param iterable<SearchSynonymEntity> $synonyms
     *
     * @return list<array<string, mixed>>
     */
    public function serializeSynonyms(iterable $synonyms): array
    {
        $serialized = [];

        foreach ($synonyms as $synonym) {
            $serialized[] = $this->serialize($synonym);
        }

        return $serialized;
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(SearchSynonymEntity $synonym): array
    {
        return [
            'id' => $synonym->getId(),
            'locale' => $synonym->getLocale(),
            'sourceTerm' => $synonym->getSourceTerm(),
            'targetTerms' => $synonym->getTargetTerms(),
            'enabled' => $synonym->isEnabled(),
            'createdAt' => $synonym->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $synonym->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
