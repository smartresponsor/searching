<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Query;

final readonly class SearchPagination
{
    public function __construct(
        public int $page = 1,
        public int $limit = 20,
    ) {
    }
}
