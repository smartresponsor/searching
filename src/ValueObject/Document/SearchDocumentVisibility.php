<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Document;

enum SearchDocumentVisibility: string
{
    case Public = 'public';
    case Internal = 'internal';
    case Private = 'private';
}
