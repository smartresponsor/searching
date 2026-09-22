<?php

declare(strict_types=1);

namespace App\Searching;

use App\Searching\DependencyInjection\Compiler\SearchableDocumentProviderPass;
use App\Searching\DependencyInjection\Compiler\SearchProviderPass;
use App\Searching\DependencyInjection\Compiler\SearchResultHydratorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Defines the searching bundle responsibility within the Searching component runtime and its typed boundaries.
 */
final class SearchingBundle extends Bundle
{
    /**
     * Builds the build used by the Searching component execution and integration boundaries.
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SearchableDocumentProviderPass());
        $container->addCompilerPass(new SearchProviderPass());
        $container->addCompilerPass(new SearchResultHydratorPass());
    }
}
