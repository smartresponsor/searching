<?php

declare(strict_types=1);

namespace App\Searching;

use App\Searching\DependencyInjection\Compiler\SearchableDocumentProviderPass;
use App\Searching\DependencyInjection\Compiler\SearchProviderPass;
use App\Searching\DependencyInjection\Compiler\SearchResultHydratorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SearchingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SearchableDocumentProviderPass());
        $container->addCompilerPass(new SearchProviderPass());
        $container->addCompilerPass(new SearchResultHydratorPass());
    }
}
