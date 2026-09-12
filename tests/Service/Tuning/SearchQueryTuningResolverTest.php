<?php

declare(strict_types=1);

namespace App\Searching\Tests\Service\Tuning;

use App\Searching\Contract\Tuning\SearchRelevanceProfileReaderInterface;
use App\Searching\Contract\Tuning\SearchSynonymReaderInterface;
use App\Searching\Entity\SearchRelevanceProfileEntity;
use App\Searching\Entity\SearchSynonymEntity;
use App\Searching\Resolver\Tuning\SearchQueryTuningResolver;
use App\Searching\Value\Query\SearchQuery;
use App\Searching\Value\Tuning\SearchRelevanceProfileCriteria;
use App\Searching\Value\Tuning\SearchSynonymCriteria;
use PHPUnit\Framework\TestCase;

final class SearchQueryTuningResolverTest extends TestCase
{
    public function testItResolvesMatchingSynonymsAndApplicableRelevanceProfiles(): void
    {
        $synonymReader = new class implements SearchSynonymReaderInterface {
            public function find(SearchSynonymCriteria $criteria): array
            {
                return [SearchSynonymEntity::create('phone', ['cell phone', 'smartphone'], $criteria->locale)];
            }

            public function count(SearchSynonymCriteria $criteria): int
            {
                return 1;
            }

            public function findOne(int $id): ?SearchSynonymEntity
            {
                return null;
            }
        };

        $profileReader = new class implements SearchRelevanceProfileReaderInterface {
            public function find(SearchRelevanceProfileCriteria $criteria): array
            {
                return [
                    SearchRelevanceProfileEntity::create('global-default', ['title' => 4]),
                    SearchRelevanceProfileEntity::create('catalog-product', ['title' => 8, 'summary' => 3], 'cataloging', 'product'),
                    SearchRelevanceProfileEntity::create('message-only', ['body' => 9], 'messaging', 'message'),
                ];
            }

            public function count(SearchRelevanceProfileCriteria $criteria): int
            {
                return 3;
            }

            public function findOne(int $id): ?SearchRelevanceProfileEntity
            {
                return null;
            }
        };

        $resolver = new SearchQueryTuningResolver($synonymReader, $profileReader);
        $tuning = $resolver->resolve(new SearchQuery(
            query: 'phone case',
            components: ['cataloging'],
            resourceTypes: ['product'],
            locale: 'en_US',
        ));

        self::assertSame(['cell phone', 'smartphone'], $tuning->expandedTerms);
        self::assertSame(8.0, $tuning->fieldWeights['title']);
        self::assertSame(3.0, $tuning->fieldWeights['summary']);
        self::assertContains('global-default', $tuning->relevanceProfiles);
        self::assertContains('catalog-product', $tuning->relevanceProfiles);
        self::assertNotContains('message-only', $tuning->relevanceProfiles);
    }
}
