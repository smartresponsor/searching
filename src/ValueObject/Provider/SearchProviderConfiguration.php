<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Provider;

/**
 * Defines the search provider configuration responsibility within the Searching component runtime and its typed boundaries.
 */
final readonly class SearchProviderConfiguration
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $nameEntity,
        public bool $enabled,
        public ?string $dsn,
        public string $indexPrefix,
        public array $options = [],
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(string $nameEntity, array $config): self
    {
        $options = isset($config['options']) && is_array($config['options']) ? $config['options'] : [];
        /** @var array<string, mixed> $options */

        return new self(
            nameEntity: $nameEntity,
            enabled: (bool) ($config['enabled'] ?? false),
            dsn: isset($config['dsn']) && is_string($config['dsn']) && '' !== $config['dsn'] ? $config['dsn'] : null,
            indexPrefix: isset($config['index_prefix']) && is_string($config['index_prefix']) && '' !== $config['index_prefix'] ? $config['index_prefix'] : 'sr',
            options: $options,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toBackendConfiguration(): array
    {
        return [
            'enabled' => $this->enabled,
            'dsn' => $this->dsn,
            'index_prefix' => $this->indexPrefix,
            'options' => $this->options,
        ];
    }
}
