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

    public function testItSkipsEmptyAndNonMatchingSynonymsAndDeduplicatesTargets(): void
    {
        $synonymReader = new class implements SearchSynonymReaderInterface {
            public int $findCalls = 0;

            public function find(SearchSynonymCriteria $criteria): array
            {
                ++$this->findCalls;

                return [
                    SearchSynonymEntity::create('', ['ignored']),
                    SearchSynonymEntity::create('tablet', ['slate']),
                    SearchSynonymEntity::create('phone', []),
                    SearchSynonymEntity::create('phone', [' smartphone ', 'smartphone', 'mobile']),
                ];
            }

            public function count(SearchSynonymCriteria $criteria): int
            {
                return 4;
            }

            public function findOne(int $id): ?SearchSynonymEntity
            {
                return null;
            }
        };
        $profileReader = new class implements SearchRelevanceProfileReaderInterface {
            public function find(SearchRelevanceProfileCriteria $criteria): array
            {
                return [];
            }

            public function count(SearchRelevanceProfileCriteria $criteria): int
            {
                return 0;
            }

            public function findOne(int $id): ?SearchRelevanceProfileEntity
            {
                return null;
            }
        };

        $tuning = (new SearchQueryTuningResolver($synonymReader, $profileReader))->resolve(new SearchQuery('PHONE case'));

        self::assertSame(1, $synonymReader->findCalls);
        self::assertSame(['smartphone', 'mobile'], $tuning->expandedTerms);
        self::assertSame(['phone' => ['smartphone', 'mobile']], $tuning->matchedSynonyms);
    }

    public function testEmptyQuerySkipsSynonymLookupAndScopedProfilesNeedMatchingQueryScope(): void
    {
        $synonymReader = new class implements SearchSynonymReaderInterface {
            public int $findCalls = 0;

            public function find(SearchSynonymCriteria $criteria): array
            {
                ++$this->findCalls;

                return [];
            }

            public function count(SearchSynonymCriteria $criteria): int
            {
                return 0;
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
                    SearchRelevanceProfileEntity::create('component-scoped', ['title' => 4], 'cataloging'),
                    SearchRelevanceProfileEntity::create('resource-scoped', ['summary' => 3], null, 'product'),
                    SearchRelevanceProfileEntity::create('global', ['body' => 2]),
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
        $unscoped = $resolver->resolve(new SearchQuery('   '));
        $mismatched = $resolver->resolve(new SearchQuery('query', components: ['messaging'], resourceTypes: ['message']));

        self::assertSame(1, $synonymReader->findCalls);
        self::assertSame([], $unscoped->expandedTerms);
        self::assertSame(['body' => 2.0], $unscoped->fieldWeights);
        self::assertSame(['global'], $unscoped->relevanceProfiles);
        self::assertSame(['global'], $mismatched->relevanceProfiles);
    }
}
