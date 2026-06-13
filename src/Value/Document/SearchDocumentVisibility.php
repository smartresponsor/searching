<?php

declare(strict_types=1);

namespace App\Searching\Value\Document;

enum SearchDocumentVisibility: string
{
    case Public = 'public';
    case Internal = 'internal';
    case Private = 'private';
}
