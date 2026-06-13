<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service;

use App\Searching\Service\SearchSurfaceContractFactory;
use App\Searching\Value\Surface\SearchSurfaceFacet;
use App\Searching\Value\Surface\SearchSurfaceResult;
use App\Searching\Value\Surface\SearchSurfaceResultItem;
use PHPUnit\Framework\TestCase;

final class SearchSurfaceContractFactoryTest extends TestCase
{
    public function testCreateUsesCanonicalSearchTemplatePath(): void
    {
        $factory = new SearchSurfaceContractFactory(new \App\Searching\Service\SearchSurfaceSerializer());
        $surface = $factory->create('catalog', new SearchSurfaceResult(
            query: 'catalog',
            total: 1,
            page: 1,
            limit: 20,
            items: [
                new SearchSurfaceResultItem(
                    component: 'cataloging',
                    resourceType: 'category',
                    resourceId: '1',
                    title: 'Electronics',
                    summary: 'Published category',
                    routeName: 'cataloging_category_show',
                    routeParameters: ['slug' => 'electronics'],
                    score: 1.0,
                ),
            ],
            facets: [
                new SearchSurfaceFacet('component', ['cataloging' => 1]),
            ],
        ));

        self::assertSame('search', $surface->word);
        self::assertSame('result', $surface->view);
        self::assertSame('search/base.html.twig', $surface->templateName());
        self::assertArrayHasKey('top.search', $surface->slotMap);
        self::assertArrayHasKey('main.body', $surface->toTemplateContext()['slots']);
        self::assertArrayHasKey('right.panel', $surface->toTemplateContext()['slots']);
    }
}
