<?php

declare(strict_types=1);

namespace App\Searching\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260914091219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the initial Searching persistence schema.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE search_index (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nameEntity VARCHAR(120) NOT NULL, provider VARCHAR(80) NOT NULL, index_name VARCHAR(191) NOT NULL, component VARCHAR(120) NOT NULL, resource_type VARCHAR(120) NOT NULL, enabled BOOLEAN NOT NULL, lifecycle_status VARCHAR(40) NOT NULL, last_lifecycle_operation VARCHAR(40) DEFAULT NULL, last_lifecycle_at DATETIME DEFAULT NULL, last_lifecycle_error CLOB DEFAULT NULL, last_indexed_at DATETIME DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX idx_search_index_enabled ON search_index (enabled)');
        $this->addSql('CREATE INDEX idx_search_index_last_indexed_at ON search_index (last_indexed_at)');
        $this->addSql('CREATE UNIQUE INDEX uniq_search_index_identity ON search_index (provider, component, resource_type)');
        $this->addSql('CREATE TABLE search_indexed_resource (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, component VARCHAR(120) NOT NULL, resource_type VARCHAR(120) NOT NULL, resource_id VARCHAR(191) NOT NULL, document_hash VARCHAR(64) DEFAULT NULL, indexed_at DATETIME DEFAULT NULL, source_updated_at DATETIME DEFAULT NULL, status VARCHAR(32) NOT NULL, error_message CLOB DEFAULT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX idx_search_indexed_resource_status ON search_indexed_resource (status)');
        $this->addSql('CREATE INDEX idx_search_indexed_resource_source_updated_at ON search_indexed_resource (source_updated_at)');
        $this->addSql('CREATE UNIQUE INDEX uniq_search_indexed_resource_identity ON search_indexed_resource (component, resource_type, resource_id)');
        $this->addSql('CREATE TABLE search_query_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, queryText VARCHAR(255) NOT NULL, userId VARCHAR(64) DEFAULT NULL, tenantId VARCHAR(64) DEFAULT NULL, correlation_id VARCHAR(64) DEFAULT NULL, request_id VARCHAR(64) DEFAULT NULL, source_component VARCHAR(120) DEFAULT NULL, source_operation VARCHAR(120) DEFAULT NULL, providerName VARCHAR(64) NOT NULL, providerTotal INTEGER NOT NULL, returnedTotal INTEGER NOT NULL, deniedCount INTEGER NOT NULL, durationMs DOUBLE PRECISION NOT NULL, successful BOOLEAN NOT NULL, errorClass VARCHAR(255) DEFAULT NULL, errorMessage CLOB DEFAULT NULL, metadata CLOB NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE search_reindex_job (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, job_key VARCHAR(64) NOT NULL, component VARCHAR(120) DEFAULT NULL, resource_type VARCHAR(120) DEFAULT NULL, status VARCHAR(32) NOT NULL, requested_by VARCHAR(191) DEFAULT NULL, changed_since DATETIME DEFAULT NULL, idempotency_key VARCHAR(64) DEFAULT NULL, dispatch_mode VARCHAR(32) DEFAULT NULL, correlation_id VARCHAR(64) DEFAULT NULL, request_id VARCHAR(64) DEFAULT NULL, source_component VARCHAR(120) DEFAULT NULL, source_operation VARCHAR(120) DEFAULT NULL, execution_context CLOB NOT NULL, queued_at DATETIME DEFAULT NULL, message_attempts INTEGER NOT NULL, last_message_failure_at DATETIME DEFAULT NULL, started_at DATETIME DEFAULT NULL, finished_at DATETIME DEFAULT NULL, provider_count INTEGER NOT NULL, processed_count INTEGER NOT NULL, failed_count INTEGER NOT NULL, errors CLOB NOT NULL, error_message CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CD5AF60D4E453838 ON search_reindex_job (job_key)');
        $this->addSql('CREATE INDEX idx_search_reindex_job_status ON search_reindex_job (status)');
        $this->addSql('CREATE INDEX idx_search_reindex_job_component_resource ON search_reindex_job (component, resource_type)');
        $this->addSql('CREATE INDEX idx_search_reindex_job_created_at ON search_reindex_job (created_at)');
        $this->addSql('CREATE INDEX idx_search_reindex_job_idempotency ON search_reindex_job (idempotency_key)');
        $this->addSql('CREATE TABLE search_relevance_profile (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nameEntity VARCHAR(128) NOT NULL, component VARCHAR(64) DEFAULT NULL, resourceType VARCHAR(64) DEFAULT NULL, fieldWeights CLOB NOT NULL, enabled BOOLEAN NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE search_synonym (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, locale VARCHAR(32) DEFAULT NULL, sourceTerm VARCHAR(255) NOT NULL, targetTerms CLOB NOT NULL, enabled BOOLEAN NOT NULL, createdAt DATETIME NOT NULL, updatedAt DATETIME NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE search_index');
        $this->addSql('DROP TABLE search_indexed_resource');
        $this->addSql('DROP TABLE search_query_log');
        $this->addSql('DROP TABLE search_reindex_job');
        $this->addSql('DROP TABLE search_relevance_profile');
        $this->addSql('DROP TABLE search_synonym');
    }
}
