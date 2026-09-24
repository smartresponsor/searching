<?php

declare(strict_types=1);

namespace App\Searching\Provider\Backend;

/**
 * Compatibility base retained for callers that referenced the earlier unavailable-provider type.
 *
 * Backend availability is now represented by SearchBackendClientInterface implementations,
 * so provider execution remains on the canonical SearchAbstractBackendProvider path.
 */
abstract class SearchAbstractUnavailableProvider extends SearchAbstractBackendProvider
{
}
