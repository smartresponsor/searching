<?php

declare(strict_types=1);

namespace App\Searching\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'search_relevance_profile')]
class SearchRelevanceProfileEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 128)]
    private string $nameEntity = '';

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $component = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $resourceType = null;

    /** @var array<string, int|float> */
    #[ORM\Column(type: 'json')]
    private array $fieldWeights = [];

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

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

    /**
     * @param array<string, int|float> $fieldWeights
     */
    public static function create(string $nameEntity, array $fieldWeights = [], ?string $component = null, ?string $resourceType = null, bool $enabled = true): self
    {
        $profile = new self();
        $profile->update($nameEntity, $fieldWeights, $component, $resourceType, $enabled);

        return $profile;
    }

    /**
     * @param array<string, int|float> $fieldWeights
     */
    public function update(string $nameEntity, array $fieldWeights = [], ?string $component = null, ?string $resourceType = null, bool $enabled = true): void
    {
        $this->nameEntity = trim($nameEntity);
        $this->fieldWeights = $this->normalizeWeights($fieldWeights);
        $this->component = null !== $component && '' !== trim($component) ? trim($component) : null;
        $this->resourceType = null !== $resourceType && '' !== trim($resourceType) ? trim($resourceType) : null;
        $this->enabled = $enabled;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->nameEntity;
    }

    public function getComponent(): ?string
    {
        return $this->component;
    }

    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    /**
     * @return array<string, int|float>
     */
    public function getFieldWeights(): array
    {
        return $this->fieldWeights;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
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
     * @param array<string, int|float> $fieldWeights
     *
     * @return array<string, int|float>
     */
    private function normalizeWeights(array $fieldWeights): array
    {
        $normalized = [];

        foreach ($fieldWeights as $field => $weight) {
            $fieldName = trim((string) $field);
            if ('' === $fieldName) {
                continue;
            }

            $normalized[$fieldName] = is_float($weight) ? $weight : (int) $weight;
        }

        return $normalized;
    }
}
