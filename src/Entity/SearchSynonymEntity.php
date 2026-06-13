<?php

declare(strict_types=1);

namespace App\Searching\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'search_synonym')]
class SearchSynonymEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $locale = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $sourceTerm = '';

    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $targetTerms = [];

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
     * @param list<string> $targetTerms
     */
    public static function create(string $sourceTerm, array $targetTerms, ?string $locale = null, bool $enabled = true): self
    {
        $synonym = new self();
        $synonym->update($sourceTerm, $targetTerms, $locale, $enabled);

        return $synonym;
    }

    /**
     * @param list<string> $targetTerms
     */
    public function update(string $sourceTerm, array $targetTerms, ?string $locale = null, bool $enabled = true): void
    {
        $this->sourceTerm = trim($sourceTerm);
        $this->targetTerms = array_values(array_filter(array_map(
            static fn (string $term): string => trim($term),
            $targetTerms,
        ), static fn (string $term): bool => '' !== $term));
        $this->locale = null !== $locale && '' !== trim($locale) ? trim($locale) : null;
        $this->enabled = $enabled;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getSourceTerm(): string
    {
        return $this->sourceTerm;
    }

    /**
     * @return list<string>
     */
    public function getTargetTerms(): array
    {
        return $this->targetTerms;
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
}
