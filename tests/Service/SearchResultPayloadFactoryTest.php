<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service;

use App\Searching\Factory\SearchResultPayloadFactory;
use App\Searching\Value\Result\SearchFacetResponse;
use App\Searching\Value\Result\SearchResponse;
use App\Searching\Value\Result\SearchResponseItem;
use PHPUnit\Framework\TestCase;

final class SearchResultPayloadFactoryTest extends TestCase
{
    public function testCreateUsesCanonicalSearchTemplatePath(): void
    {
        $factory = new SearchResultPayloadFactory(new \App\Searching\Service\SearchResponseSerializer());
        $payload = $factory->create('catalog', new SearchResponse(
            query: 'catalog',
            total: 1,
            page: 1,
            limit: 20,
            items: [
                new SearchResponseItem(
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
                new SearchFacetResponse('component', ['cataloging' => 1]),
            ],
        ));

        self::assertSame('search', $payload->word);
        self::assertSame('result', $payload->view);
        self::assertSame('search/base.html.twig', $payload->templateName());
        self::assertArrayHasKey('top.search', $payload->slotMap);
        self::assertArrayHasKey('main.body', $payload->toTemplateContext()['slots']);
        self::assertArrayHasKey('right.panel', $payload->toTemplateContext()['slots']);
    }
}
