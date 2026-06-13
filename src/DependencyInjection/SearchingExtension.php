<?php

declare(strict_types=1);

namespace App\Searching\DependencyInjection;

use App\Searching\Command\SearchDocumentRemoveCommand;
use App\Searching\Command\SearchHealthCommand;
use App\Searching\Command\SearchIndexedResourceListCommand;
use App\Searching\Command\SearchIndexLifecycleCommand;
use App\Searching\Command\SearchIndexListCommand;
use App\Searching\Command\SearchIndexRebuildCommand;
use App\Searching\Command\SearchProviderStatusCommand;
use App\Searching\Command\SearchQueryLogListCommand;
use App\Searching\Command\SearchReindexEnqueueCommand;
use App\Searching\Command\SearchReindexJobListCommand;
use App\Searching\Controller\Admin\SearchBridgeAdminController;
use App\Searching\Controller\Admin\SearchHealthAdminController;
use App\Searching\Controller\Admin\SearchIndexAdminController;
use App\Searching\Controller\Admin\SearchIndexedResourceAdminController;
use App\Searching\Controller\Admin\SearchQueryLogAdminController;
use App\Searching\Controller\Admin\SearchReindexAdminController;
use App\Searching\Controller\Admin\SearchReindexJobAdminController;
use App\Searching\Controller\Admin\SearchRelevanceProfileAdminController;
use App\Searching\Controller\Admin\SearchSynonymAdminController;
use App\Searching\Controller\Api\SearchApiController;
use App\Searching\Controller\Api\SearchBridgeApiController;
use App\Searching\Controller\Api\SearchHealthApiController;
use App\Searching\Controller\Api\SearchIndexApiController;
use App\Searching\Controller\Api\SearchIndexedResourceApiController;
use App\Searching\Controller\Api\SearchIndexLifecycleApiController;
use App\Searching\Controller\Api\SearchProviderStatusApiController;
use App\Searching\Controller\Api\SearchQueryLogApiController;
use App\Searching\Controller\Api\SearchReindexApiController;
use App\Searching\Controller\Api\SearchReindexJobApiController;
use App\Searching\Controller\Api\SearchRelevanceProfileApiController;
use App\Searching\Controller\Api\SearchResourceApiController;
use App\Searching\Controller\Api\SearchSuggestionApiController;
use App\Searching\Controller\Api\SearchSurfaceApiController;
use App\Searching\Controller\Api\SearchSynonymApiController;
use App\Searching\Handler\SearchReindexMessageHandler;
use App\Searching\Repository\SearchIndexedResourceRepository;
use App\Searching\Repository\SearchIndexRepository;
use App\Searching\Repository\SearchQueryLogRepository;
use App\Searching\Repository\SearchReindexJobRepository;
use App\Searching\Repository\SearchRelevanceProfileRepository;
use App\Searching\Repository\SearchSynonymRepository;
use App\Searching\Service\Bridge\InterfacingSearchBridgeProvider;
use App\Searching\Service\Bridge\SearchBridgeSurfaceConfigSerializer;
use App\Searching\Service\Flow\NullSearchOperationLimiter;
use App\Searching\Service\Flow\SearchOperationLimiter;
use App\Searching\Service\Health\SearchHealthChecker;
use App\Searching\Service\Indexing\DoctrineSearchIndexedResourceTracker;
use App\Searching\Service\Indexing\DoctrineSearchIndexWriter;
use App\Searching\Service\Indexing\DoctrineSearchReindexJobTracker;
use App\Searching\Service\Indexing\MessengerSearchReindexDispatcher;
use App\Searching\Service\Indexing\NullSearchIndexedResourceTracker;
use App\Searching\Service\Indexing\NullSearchIndexLifecycleRegistrySynchronizer;
use App\Searching\Service\Indexing\NullSearchReindexDuplicateGuard;
use App\Searching\Service\Indexing\NullSearchReindexJobTracker;
use App\Searching\Service\Indexing\SearchDocumentFingerprintCalculator;
use App\Searching\Service\Indexing\SearchDocumentIndexer;
use App\Searching\Service\Indexing\SearchDocumentNormalizer;
use App\Searching\Service\Indexing\SearchIncrementalIndexer;
use App\Searching\Service\Indexing\SearchIndexedResourceReader;
use App\Searching\Service\Indexing\SearchIndexLifecycleManager;
use App\Searching\Service\Indexing\SearchIndexLifecycleRegistrySynchronizer;
use App\Searching\Service\Indexing\SearchIndexReader;
use App\Searching\Service\Indexing\SearchReindexCoordinator;
use App\Searching\Service\Indexing\SearchReindexDuplicateGuard;
use App\Searching\Service\Indexing\SearchReindexIdempotencyKeyBuilder;
use App\Searching\Service\Indexing\SearchReindexJobReader;
use App\Searching\Service\Indexing\SyncSearchReindexDispatcher;
use App\Searching\Service\Observability\SearchExecutionContextResolver;
use App\Searching\Service\Provider\ElasticsearchSearchProvider;
use App\Searching\Service\Provider\NullSearchProvider;
use App\Searching\Service\Provider\OpenSearchSearchProvider;
use App\Searching\Service\Provider\SearchBackendQueryBuilder;
use App\Searching\Service\Provider\SearchBackendSuggestionBuilder;
use App\Searching\Service\Provider\SearchBulkOperationBuilder;
use App\Searching\Service\Provider\SearchDocumentPayloadMapper;
use App\Searching\Service\Provider\SearchIndexMappingBuilder;
use App\Searching\Service\Provider\SearchIndexNameBuilder;
use App\Searching\Service\Provider\SearchProviderStatusCollector;
use App\Searching\Service\Provider\SearchQueryPayloadMapper;
use App\Searching\Service\Provider\UnavailableSearchBackendClient;
use App\Searching\Service\Query\DoctrineSearchQueryLogger;
use App\Searching\Service\Query\NullSearchQueryLogger;
use App\Searching\Service\Query\SearchQueryExecutor;
use App\Searching\Service\Query\SearchQueryLogReader;
use App\Searching\Service\Query\SearchResultHydrator;
use App\Searching\Service\Query\SearchSuggestionProvider;
use App\Searching\Service\Registry\SearchableResourceRegistry;
use App\Searching\Service\Registry\SearchProviderRegistry;
use App\Searching\Service\SearchingSearchSurfaceProvider;
use App\Searching\Service\SearchResultSurfaceBuilder;
use App\Searching\Service\SearchSurfaceContractFactory;
use App\Searching\Service\SearchSurfaceMapper;
use App\Searching\Service\SearchSurfaceSerializer;
use App\Searching\Service\Security\SearchPermissionChecker;
use App\Searching\Service\Security\SearchPermissionFilter;
use App\Searching\Service\Serialization\SearchHealthReportSerializer;
use App\Searching\Service\Serialization\SearchIndexedResourceSerializer;
use App\Searching\Service\Serialization\SearchIndexLifecycleResultSerializer;
use App\Searching\Service\Serialization\SearchIndexSerializer;
use App\Searching\Service\Serialization\SearchProviderStatusSerializer;
use App\Searching\Service\Serialization\SearchQueryExecutionTraceSerializer;
use App\Searching\Service\Serialization\SearchQueryLogSerializer;
use App\Searching\Service\Serialization\SearchRegistrySerializer;
use App\Searching\Service\Serialization\SearchReindexDispatchResultSerializer;
use App\Searching\Service\Serialization\SearchReindexJobSerializer;
use App\Searching\Service\Serialization\SearchReindexResultSerializer;
use App\Searching\Service\Serialization\SearchRelevanceProfileSerializer;
use App\Searching\Service\Serialization\SearchResultSerializer;
use App\Searching\Service\Serialization\SearchSynonymSerializer;
use App\Searching\Service\Tuning\DoctrineSearchRelevanceProfileWriter;
use App\Searching\Service\Tuning\DoctrineSearchSynonymWriter;
use App\Searching\Service\Tuning\NullSearchQueryTuningResolver;
use App\Searching\Service\Tuning\SearchQueryTuningResolver;
use App\Searching\Service\Tuning\SearchRelevanceProfileReader;
use App\Searching\Service\Tuning\SearchSynonymReader;
use App\Searching\ServiceInterface\Bridge\InterfacingSearchBridgeProviderInterface;
use App\Searching\ServiceInterface\Flow\SearchOperationLimiterInterface;
use App\Searching\ServiceInterface\Health\SearchHealthCheckerInterface;
use App\Searching\ServiceInterface\Indexing\SearchDocumentIndexerInterface;
use App\Searching\ServiceInterface\Indexing\SearchIncrementalIndexerInterface;
use App\Searching\ServiceInterface\Indexing\SearchIndexedResourceReaderInterface;
use App\Searching\ServiceInterface\Indexing\SearchIndexedResourceTrackerInterface;
use App\Searching\ServiceInterface\Indexing\SearchIndexLifecycleManagerInterface;
use App\Searching\ServiceInterface\Indexing\SearchIndexLifecycleRegistrySynchronizerInterface;
use App\Searching\ServiceInterface\Indexing\SearchIndexReaderInterface;
use App\Searching\ServiceInterface\Indexing\SearchIndexWriterInterface;
use App\Searching\ServiceInterface\Indexing\SearchReindexCoordinatorInterface;
use App\Searching\ServiceInterface\Indexing\SearchReindexDispatcherInterface;
use App\Searching\ServiceInterface\Indexing\SearchReindexDuplicateGuardInterface;
use App\Searching\ServiceInterface\Indexing\SearchReindexJobReaderInterface;
use App\Searching\ServiceInterface\Indexing\SearchReindexJobTrackerInterface;
use App\Searching\ServiceInterface\Observability\SearchExecutionContextResolverInterface;
use App\Searching\ServiceInterface\Producer\SearchableDocumentProviderInterface;
use App\Searching\ServiceInterface\Producer\SearchResultItemHydratorInterface;
use App\Searching\ServiceInterface\Provider\SearchBackendClientInterface;
use App\Searching\ServiceInterface\Provider\SearchBackendQueryBuilderInterface;
use App\Searching\ServiceInterface\Provider\SearchBackendSuggestionBuilderInterface;
use App\Searching\ServiceInterface\Provider\SearchBulkOperationBuilderInterface;
use App\Searching\ServiceInterface\Provider\SearchIndexMappingBuilderInterface;
use App\Searching\ServiceInterface\Provider\SearchProviderInterface;
use App\Searching\ServiceInterface\Query\SearchQueryExecutorInterface;
use App\Searching\ServiceInterface\Query\SearchQueryLoggerInterface;
use App\Searching\ServiceInterface\Query\SearchQueryLogReaderInterface;
use App\Searching\ServiceInterface\Query\SearchResultHydratorInterface;
use App\Searching\ServiceInterface\Query\SearchSuggestionProviderInterface;
use App\Searching\ServiceInterface\Registry\SearchableResourceRegistryInterface;
use App\Searching\ServiceInterface\Security\SearchPermissionCheckerInterface;
use App\Searching\ServiceInterface\Security\SearchPermissionFilterInterface;
use App\Searching\ServiceInterface\Surface\SearchSuggestionSurfaceProviderInterface;
use App\Searching\ServiceInterface\Surface\SearchSurfaceProviderInterface;
use App\Searching\ServiceInterface\Tuning\SearchQueryTuningResolverInterface;
use App\Searching\ServiceInterface\Tuning\SearchRelevanceProfileReaderInterface;
use App\Searching\ServiceInterface\Tuning\SearchRelevanceProfileWriterInterface;
use App\Searching\ServiceInterface\Tuning\SearchSynonymReaderInterface;
use App\Searching\ServiceInterface\Tuning\SearchSynonymWriterInterface;
use App\Searching\Subscriber\SearchDocumentChangeSubscriber;
use App\Searching\Value\Provider\SearchProviderConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class SearchingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('searching.enabled', $config['enabled']);
        $container->setParameter('searching.default_provider', $config['default_provider']);
        $container->setParameter('searching.providers', $config['providers']);
        $container->setParameter('searching.query.default_limit', $config['query']['default_limit']);
        $container->setParameter('searching.query.max_limit', $config['query']['max_limit']);
        $container->setParameter('searching.indexing.batch_size', $config['indexing']['batch_size']);
        $container->setParameter('searching.indexing.reindex.dispatch_mode', $config['indexing']['reindex']['dispatch_mode']);
        $container->setParameter('searching.indexing.reindex.messenger_max_attempts', $config['indexing']['reindex']['messenger_max_attempts']);
        $container->setParameter('searching.indexing.reindex.duplicate_guard', $config['indexing']['reindex']['duplicate_guard']);
        $container->setParameter('searching.logging.enabled', $config['logging']['enabled']);
        $container->setParameter('searching.logging.driver', $config['logging']['driver']);
        $container->setParameter('searching.logging.recent_limit', $config['logging']['recent_limit']);
        $container->setParameter('searching.hydration.enabled', $config['hydration']['enabled']);
        $container->setParameter('searching.security.permission_filtering', $config['security']['permission_filtering']);
        $container->setParameter('searching.flow_control.enabled', $config['flow_control']['enabled']);
        $container->setParameter('searching.flow_control.operations', $config['flow_control']['operations']);

        $container->registerForAutoconfiguration(SearchableDocumentProviderInterface::class)
            ->addTag('searching.searchable_document_provider');

        $container->registerForAutoconfiguration(SearchResultItemHydratorInterface::class)
            ->addTag('searching.search_result_hydrator');

        $container->register(NullSearchOperationLimiter::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchOperationLimiter::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$operationLimits', $config['flow_control']['operations'])
            ->setArgument('$defaultMode', (string) $config['flow_control']['default_mode']);

        $operationLimiterClass = (bool) $config['flow_control']['enabled']
            ? SearchOperationLimiter::class
            : NullSearchOperationLimiter::class;

        $container->setAlias(SearchOperationLimiterInterface::class, $operationLimiterClass)
            ->setPublic(false);

        $container->register(SearchExecutionContextResolver::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(SearchExecutionContextResolverInterface::class, SearchExecutionContextResolver::class)
            ->setPublic(false);

        $container->register(NullSearchProvider::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->addTag('searching.provider', ['nameEntity' => 'null']);

        $container->register(UnavailableSearchBackendClient::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(SearchBackendClientInterface::class, UnavailableSearchBackendClient::class)
            ->setPublic(false);

        $container->register(SearchIndexNameBuilder::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchIndexMappingBuilder::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(SearchIndexMappingBuilderInterface::class, SearchIndexMappingBuilder::class)
            ->setPublic(false);

        $container->register(SearchBackendQueryBuilder::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$tuningResolver', new Reference(SearchQueryTuningResolverInterface::class));

        $container->setAlias(SearchBackendQueryBuilderInterface::class, SearchBackendQueryBuilder::class)
            ->setPublic(false);

        $container->register(SearchBackendSuggestionBuilder::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$tuningResolver', new Reference(SearchQueryTuningResolverInterface::class));

        $container->setAlias(SearchBackendSuggestionBuilderInterface::class, SearchBackendSuggestionBuilder::class)
            ->setPublic(false);

        $container->register(SearchBulkOperationBuilder::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(SearchBulkOperationBuilderInterface::class, SearchBulkOperationBuilder::class)
            ->setPublic(false);

        $container->register(SearchDocumentPayloadMapper::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchQueryPayloadMapper::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $this->registerBackendProvider($container, 'elasticsearch', ElasticsearchSearchProvider::class, $config['providers']['elasticsearch'] ?? []);
        $this->registerBackendProvider($container, 'opensearch', OpenSearchSearchProvider::class, $config['providers']['opensearch'] ?? []);

        $defaultProviderClass = match ($config['default_provider']) {
            'elasticsearch' => ElasticsearchSearchProvider::class,
            'opensearch' => OpenSearchSearchProvider::class,
            default => NullSearchProvider::class,
        };

        $container->setAlias(SearchProviderInterface::class, $defaultProviderClass)
            ->setPublic(false);

        $container->register(SearchProviderRegistry::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchIndexLifecycleRegistrySynchronizer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(NullSearchIndexLifecycleRegistrySynchronizer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(SearchIndexLifecycleRegistrySynchronizerInterface::class, SearchIndexLifecycleRegistrySynchronizer::class)
            ->setPublic(false);

        $container->register(SearchIndexLifecycleManager::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$providerRegistry', new Reference(SearchProviderRegistry::class))
            ->setArgument('$indexNameBuilder', new Reference(SearchIndexNameBuilder::class))
            ->setArgument('$defaultIndexPrefix', (string) ($config['providers'][$config['default_provider']]['index_prefix'] ?? 'sr'))
            ->setArgument('$registrySynchronizer', new Reference(SearchIndexLifecycleRegistrySynchronizerInterface::class));

        $container->setAlias(SearchIndexLifecycleManagerInterface::class, SearchIndexLifecycleManager::class)
            ->setPublic(false);

        foreach ([
            SearchIndexRepository::class,
            SearchIndexReader::class,
            DoctrineSearchIndexWriter::class,
            SearchIndexSerializer::class,
        ] as $serviceClass) {
            $container->register($serviceClass)
                ->setAutowired(true)
                ->setAutoconfigured(true);
        }

        $container->setAlias(SearchIndexReaderInterface::class, SearchIndexReader::class)
            ->setPublic(false);

        $container->setAlias(SearchIndexWriterInterface::class, DoctrineSearchIndexWriter::class)
            ->setPublic(false);

        $container->register(SearchPermissionChecker::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(SearchPermissionCheckerInterface::class, SearchPermissionChecker::class)
            ->setPublic(false);

        $container->register(SearchPermissionFilter::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$permissionChecker', new Reference(SearchPermissionCheckerInterface::class));

        $container->setAlias(SearchPermissionFilterInterface::class, SearchPermissionFilter::class)
            ->setPublic(false);

        $container->register(NullSearchQueryLogger::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(DoctrineSearchQueryLogger::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$flushImmediately', (bool) $config['logging']['flush_immediately']);

        $loggerClass = ((bool) $config['logging']['enabled'] && 'doctrine' === $config['logging']['driver'])
            ? DoctrineSearchQueryLogger::class
            : NullSearchQueryLogger::class;

        $container->setAlias(SearchQueryLoggerInterface::class, $loggerClass)
            ->setPublic(false);

        $container->register(SearchQueryLogRepository::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchQueryLogReader::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(SearchQueryLogReaderInterface::class, SearchQueryLogReader::class)
            ->setPublic(false);

        $container->register(SearchResultHydrator::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(SearchResultHydratorInterface::class, SearchResultHydrator::class)
            ->setPublic(false);

        foreach ([
            SearchSynonymRepository::class,
            SearchRelevanceProfileRepository::class,
            SearchSynonymReader::class,
            DoctrineSearchSynonymWriter::class,
            SearchRelevanceProfileReader::class,
            DoctrineSearchRelevanceProfileWriter::class,
            SearchQueryTuningResolver::class,
            NullSearchQueryTuningResolver::class,
            SearchSynonymSerializer::class,
            SearchRelevanceProfileSerializer::class,
        ] as $serviceClass) {
            $container->register($serviceClass)
                ->setAutowired(true)
                ->setAutoconfigured(true);
        }

        $container->setAlias(SearchSynonymReaderInterface::class, SearchSynonymReader::class)
            ->setPublic(false);

        $container->setAlias(SearchSynonymWriterInterface::class, DoctrineSearchSynonymWriter::class)
            ->setPublic(false);

        $container->setAlias(SearchRelevanceProfileReaderInterface::class, SearchRelevanceProfileReader::class)
            ->setPublic(false);

        $container->setAlias(SearchRelevanceProfileWriterInterface::class, DoctrineSearchRelevanceProfileWriter::class)
            ->setPublic(false);

        $container->setAlias(SearchQueryTuningResolverInterface::class, SearchQueryTuningResolver::class)
            ->setPublic(false);

        $container->register(SearchQueryExecutor::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$searchProvider', new Reference(SearchProviderInterface::class))
            ->setArgument('$resultHydrator', new Reference(SearchResultHydratorInterface::class))
            ->setArgument('$permissionFilter', new Reference(SearchPermissionFilterInterface::class))
            ->setArgument('$queryLogger', new Reference(SearchQueryLoggerInterface::class))
            ->setArgument('$operationLimiter', new Reference(SearchOperationLimiterInterface::class))
            ->setArgument('$resultHydration', (bool) $config['hydration']['enabled'])
            ->setArgument('$permissionFiltering', (bool) $config['security']['permission_filtering']);

        $container->setAlias(SearchQueryExecutorInterface::class, SearchQueryExecutor::class)
            ->setPublic(false);

        $container->register(SearchSuggestionProvider::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$searchProvider', new Reference(SearchProviderInterface::class));

        $container->setAlias(SearchSuggestionProviderInterface::class, SearchSuggestionProvider::class)
            ->setPublic(false);

        $container->register(SearchResultSerializer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchRegistrySerializer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchProviderStatusSerializer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchQueryExecutionTraceSerializer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchQueryLogSerializer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchReindexResultSerializer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchIndexLifecycleResultSerializer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchProviderStatusCollector::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchHealthChecker::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(SearchHealthCheckerInterface::class, SearchHealthChecker::class)
            ->setPublic(false);

        $container->register(SearchHealthReportSerializer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        foreach ([
            SearchSurfaceMapper::class,
            SearchSurfaceSerializer::class,
            SearchSurfaceContractFactory::class,
            SearchingSearchSurfaceProvider::class,
            InterfacingSearchBridgeProvider::class,
            SearchBridgeSurfaceConfigSerializer::class,
        ] as $surfaceServiceClass) {
            $container->register($surfaceServiceClass)
                ->setAutowired(true)
                ->setAutoconfigured(true);
        }

        $container->register(SearchResultSurfaceBuilder::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setPublic(true);

        $container->setAlias(SearchSurfaceProviderInterface::class, SearchingSearchSurfaceProvider::class)
            ->setPublic(false);

        $container->setAlias(SearchSuggestionSurfaceProviderInterface::class, SearchingSearchSurfaceProvider::class)
            ->setPublic(false);

        $container->setAlias(InterfacingSearchBridgeProviderInterface::class, InterfacingSearchBridgeProvider::class)
            ->setPublic(false);

        $container->register(SearchableResourceRegistry::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(SearchableResourceRegistryInterface::class, SearchableResourceRegistry::class)
            ->setPublic(false);

        $container->register(SearchDocumentNormalizer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchDocumentFingerprintCalculator::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchIndexedResourceRepository::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(SearchIndexedResourceReader::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(SearchIndexedResourceReaderInterface::class, SearchIndexedResourceReader::class)
            ->setPublic(false);

        $container->register(SearchIndexedResourceSerializer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(NullSearchIndexedResourceTracker::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->register(DoctrineSearchIndexedResourceTracker::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$flushImmediately', true);

        $indexedResourceTrackerClass = (bool) $config['indexing']['track_document_hash']
            ? DoctrineSearchIndexedResourceTracker::class
            : NullSearchIndexedResourceTracker::class;

        $container->setAlias(SearchIndexedResourceTrackerInterface::class, $indexedResourceTrackerClass)
            ->setPublic(false);

        $container->register(SearchDocumentIndexer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$searchProvider', new Reference(SearchProviderInterface::class))
            ->setArgument('$documentNormalizer', new Reference(SearchDocumentNormalizer::class))
            ->setArgument('$fingerprintCalculator', new Reference(SearchDocumentFingerprintCalculator::class))
            ->setArgument('$indexedResourceTracker', new Reference(SearchIndexedResourceTrackerInterface::class));

        $container->setAlias(SearchDocumentIndexerInterface::class, SearchDocumentIndexer::class)
            ->setPublic(false);

        $container->register(SearchIncrementalIndexer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$documentIndexer', new Reference(SearchDocumentIndexerInterface::class))
            ->setArgument('$searchProvider', new Reference(SearchProviderInterface::class))
            ->setArgument('$fingerprintCalculator', new Reference(SearchDocumentFingerprintCalculator::class))
            ->setArgument('$indexedResourceTracker', new Reference(SearchIndexedResourceTrackerInterface::class));

        $container->setAlias(SearchIncrementalIndexerInterface::class, SearchIncrementalIndexer::class)
            ->setPublic(false);

        $container->register(SearchDocumentChangeSubscriber::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->addTag('kernel.event_subscriber');

        foreach ([
            SearchReindexJobRepository::class,
            SearchReindexJobReader::class,
            DoctrineSearchReindexJobTracker::class,
            NullSearchReindexJobTracker::class,
            SearchReindexIdempotencyKeyBuilder::class,
            SearchReindexDuplicateGuard::class,
            NullSearchReindexDuplicateGuard::class,
            SearchReindexJobSerializer::class,
        ] as $serviceClass) {
            $container->register($serviceClass)
                ->setAutowired(true)
                ->setAutoconfigured(true);
        }

        $container->setAlias(SearchReindexJobReaderInterface::class, SearchReindexJobReader::class)
            ->setPublic(false);

        $container->setAlias(SearchReindexJobTrackerInterface::class, DoctrineSearchReindexJobTracker::class)
            ->setPublic(false);

        $duplicateGuardClass = (bool) $config['indexing']['reindex']['duplicate_guard']
            ? SearchReindexDuplicateGuard::class
            : NullSearchReindexDuplicateGuard::class;

        $container->setAlias(SearchReindexDuplicateGuardInterface::class, $duplicateGuardClass)
            ->setPublic(false);

        $container->register(SearchReindexCoordinator::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$resourceRegistry', new Reference(SearchableResourceRegistryInterface::class))
            ->setArgument('$documentIndexer', new Reference(SearchDocumentIndexerInterface::class))
            ->setArgument('$jobTracker', new Reference(SearchReindexJobTrackerInterface::class));

        $container->setAlias(SearchReindexCoordinatorInterface::class, SearchReindexCoordinator::class)
            ->setPublic(false);

        $container->register(SyncSearchReindexDispatcher::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$coordinator', new Reference(SearchReindexCoordinatorInterface::class))
            ->setArgument('$operationLimiter', new Reference(SearchOperationLimiterInterface::class))
            ->setArgument('$idempotencyKeyBuilder', new Reference(SearchReindexIdempotencyKeyBuilder::class));

        $reindexDispatcherClass = SyncSearchReindexDispatcher::class;

        if ('messenger' === $config['indexing']['reindex']['dispatch_mode']) {
            $container->register(MessengerSearchReindexDispatcher::class)
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setArgument('$jobTracker', new Reference(SearchReindexJobTrackerInterface::class))
                ->setArgument('$idempotencyKeyBuilder', new Reference(SearchReindexIdempotencyKeyBuilder::class))
                ->setArgument('$duplicateGuard', new Reference(SearchReindexDuplicateGuardInterface::class))
                ->setArgument('$operationLimiter', new Reference(SearchOperationLimiterInterface::class))
                ->setArgument('$maxAttempts', (int) $config['indexing']['reindex']['messenger_max_attempts']);

            $container->register(SearchReindexMessageHandler::class)
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setArgument('$coordinator', new Reference(SearchReindexCoordinatorInterface::class))
                ->setArgument('$jobTracker', new Reference(SearchReindexJobTrackerInterface::class))
                ->addTag('messenger.message_handler');

            $reindexDispatcherClass = MessengerSearchReindexDispatcher::class;
        }

        $container->setAlias(SearchReindexDispatcherInterface::class, $reindexDispatcherClass)
            ->setPublic(false);

        $container->register(SearchReindexDispatchResultSerializer::class)
            ->setAutowired(true)
            ->setAutoconfigured(true);

        foreach ([
            SearchDocumentRemoveCommand::class,
            SearchIndexLifecycleCommand::class,
            SearchIndexListCommand::class,
            SearchIndexedResourceListCommand::class,
            SearchIndexRebuildCommand::class,
            SearchProviderStatusCommand::class,
            SearchQueryLogListCommand::class,
            SearchReindexJobListCommand::class,
            SearchReindexEnqueueCommand::class,
            SearchHealthCommand::class,
        ] as $commandClass) {
            $container->register($commandClass)
                ->setAutowired(true)
                ->setAutoconfigured(true);
        }

        foreach ([
            SearchApiController::class,
            SearchSuggestionApiController::class,
            SearchResourceApiController::class,
            SearchIndexApiController::class,
            SearchIndexLifecycleApiController::class,
            SearchIndexedResourceApiController::class,
            SearchProviderStatusApiController::class,
            SearchQueryLogApiController::class,
            SearchRelevanceProfileApiController::class,
            SearchSynonymApiController::class,
            SearchReindexApiController::class,
            SearchReindexJobApiController::class,
            SearchIndexAdminController::class,
            SearchIndexedResourceAdminController::class,
            SearchQueryLogAdminController::class,
            SearchRelevanceProfileAdminController::class,
            SearchSynonymAdminController::class,
            SearchReindexAdminController::class,
            SearchReindexJobAdminController::class,
            SearchHealthApiController::class,
            SearchHealthAdminController::class,
            SearchBridgeApiController::class,
            SearchBridgeAdminController::class,
            SearchSurfaceApiController::class,
        ] as $controllerClass) {
            $container->register($controllerClass)
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setPublic(true)
                ->addTag('controller.service_arguments');
        }

        $container->getDefinition(SearchQueryLogApiController::class)
            ->setArgument('$defaultLimit', (int) $config['logging']['recent_limit']);

        $container->getDefinition(SearchQueryLogAdminController::class)
            ->setArgument('$defaultLimit', (int) $config['logging']['recent_limit']);

        $container->getDefinition(SearchReindexJobApiController::class)
            ->setArgument('$defaultLimit', 50);

        $container->getDefinition(SearchReindexJobAdminController::class)
            ->setArgument('$defaultLimit', 50);

        $container->getDefinition(SearchIndexApiController::class)
            ->setArgument('$defaultLimit', 50);

        $container->getDefinition(SearchIndexAdminController::class)
            ->setArgument('$defaultLimit', 50);

        $container->getDefinition(SearchIndexedResourceApiController::class)
            ->setArgument('$defaultLimit', 50);

        $container->getDefinition(SearchIndexedResourceAdminController::class)
            ->setArgument('$defaultLimit', 50);

        foreach ([
            SearchRelevanceProfileApiController::class,
            SearchRelevanceProfileAdminController::class,
            SearchSynonymApiController::class,
            SearchSynonymAdminController::class,
        ] as $tuningControllerClass) {
            $container->getDefinition($tuningControllerClass)
                ->setArgument('$defaultLimit', 50);
        }
    }

    /**
     * @param class-string         $providerClass
     * @param array<string, mixed> $providerConfig
     */
    private function registerBackendProvider(ContainerBuilder $container, string $nameEntity, string $providerClass, array $providerConfig): void
    {
        $configurationServiceId = sprintf('searching.provider_configuration.%s', $nameEntity);

        $container->register($configurationServiceId, SearchProviderConfiguration::class)
            ->setFactory([SearchProviderConfiguration::class, 'fromArray'])
            ->setArguments([$nameEntity, $providerConfig]);

        $container->register($providerClass)
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->setArgument('$configuration', new Reference($configurationServiceId))
            ->setArgument('$indexNameBuilder', new Reference(SearchIndexNameBuilder::class))
            ->setArgument('$documentPayloadMapper', new Reference(SearchDocumentPayloadMapper::class))
            ->setArgument('$queryBuilder', new Reference(SearchBackendQueryBuilderInterface::class))
            ->setArgument('$suggestionBuilder', new Reference(SearchBackendSuggestionBuilderInterface::class))
            ->setArgument('$bulkOperationBuilder', new Reference(SearchBulkOperationBuilderInterface::class))
            ->setArgument('$mappingBuilder', new Reference(SearchIndexMappingBuilderInterface::class))
            ->setArgument('$backendClient', new Reference(SearchBackendClientInterface::class))
            ->addTag('searching.provider', ['nameEntity' => $nameEntity]);
    }
}
