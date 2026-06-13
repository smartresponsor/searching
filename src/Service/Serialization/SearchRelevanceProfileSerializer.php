<?php

declare(strict_types=1);

namespace App\Searching\Service\Serialization;

use App\Searching\Entity\SearchRelevanceProfileEntity;

final class SearchRelevanceProfileSerializer
{
    /**
     * @param iterable<SearchRelevanceProfileEntity> $profiles
     *
     * @return list<array<string, mixed>>
     */
    public function serializeProfiles(iterable $profiles): array
    {
        $serialized = [];

        foreach ($profiles as $profile) {
            $serialized[] = $this->serialize($profile);
        }

        return $serialized;
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(SearchRelevanceProfileEntity $profile): array
    {
        return [
            'id' => $profile->getId(),
            'nameEntity' => $profile->getName(),
            'component' => $profile->getComponent(),
            'resourceType' => $profile->getResourceType(),
            'fieldWeights' => $profile->getFieldWeights(),
            'enabled' => $profile->isEnabled(),
            'createdAt' => $profile->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'updatedAt' => $profile->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
