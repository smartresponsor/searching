<?php

declare(strict_types=1);

namespace App\Searching\ValueObject\Document;

/**
 * Defines the search document visibility responsibility within the Searching component runtime and its typed boundaries.
 */
enum SearchDocumentVisibility: string
{
    case Public = 'public';
    case Internal = 'internal';
    case Private = 'private';
}
