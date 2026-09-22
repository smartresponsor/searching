<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Result;

final readonly class SearchResultPayload
{
    public const WORD = 'search';
    public const VIEW_RESULT = 'result';

    /**
     * @param array<string, string>     $slotMap
     * @param array<string, mixed>      $slots
     * @param array<string, mixed>|null $result
     * @param array<string, mixed>|null $error
     */
    public function __construct(
        public string $word,
        public string $view,
        public string $templateName,
        public array $slotMap,
        public string $query,
        public ?array $result,
        public ?array $error,
        public array $slots,
    ) {
    }

    /**
     * @return array{word: string, view: string, templateName: string, slotMap: array<string, string>, query: string, result: array<string, mixed>|null, error: array<string, mixed>|null, slots: array<string, mixed>}
     */
    public function toTemplateContext(): array
    {
        return [
            'word' => $this->word,
            'view' => $this->view,
            'templateName' => $this->templateName,
            'slotMap' => $this->slotMap,
            'query' => $this->query,
            'result' => $this->result,
            'error' => $this->error,
            'slots' => $this->slots,
        ];
    }

    /**
     * @return array{word: string, view: string, query: string, result: array<string, mixed>|null, error: array<string, mixed>|null, slots: array<string, mixed>}
     */
    public function toFallbackData(): array
    {
        return [
            'word' => $this->word,
            'view' => $this->view,
            'query' => $this->query,
            'result' => $this->result,
            'error' => $this->error,
            'slots' => $this->slots,
        ];
    }

    public function templateName(): string
    {
        return $this->templateName;
    }
}
