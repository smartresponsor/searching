<?php

declare(strict_types=1);

namespace App\Searching\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260923183504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize Searching Doctrine physical identifiers to lower_snake_case and give the reindex job key constraint a deterministic name.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE search_index RENAME COLUMN nameEntity TO name_entity');
        $this->addSql('ALTER TABLE search_index RENAME COLUMN createdAt TO created_at');
        $this->addSql('ALTER TABLE search_index RENAME COLUMN updatedAt TO updated_at');

        $this->addSql('ALTER TABLE search_indexed_resource RENAME COLUMN createdAt TO created_at');
        $this->addSql('ALTER TABLE search_indexed_resource RENAME COLUMN updatedAt TO updated_at');

        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN queryText TO query_text');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN userId TO user_id');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN providerName TO provider_name');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN providerTotal TO provider_total');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN returnedTotal TO returned_total');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN deniedCount TO denied_count');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN durationMs TO duration_ms');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN errorClass TO error_class');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN errorMessage TO error_message');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN createdAt TO created_at');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN updatedAt TO updated_at');

        $this->addSql('DROP INDEX UNIQ_CD5AF60D4E453838');
        $this->addSql('CREATE UNIQUE INDEX uniq_search_reindex_job_job_key ON search_reindex_job (job_key)');

        $this->addSql('ALTER TABLE search_relevance_profile RENAME COLUMN nameEntity TO name_entity');
        $this->addSql('ALTER TABLE search_relevance_profile RENAME COLUMN resourceType TO resource_type');
        $this->addSql('ALTER TABLE search_relevance_profile RENAME COLUMN fieldWeights TO field_weights');
        $this->addSql('ALTER TABLE search_relevance_profile RENAME COLUMN createdAt TO created_at');
        $this->addSql('ALTER TABLE search_relevance_profile RENAME COLUMN updatedAt TO updated_at');

        $this->addSql('ALTER TABLE search_synonym RENAME COLUMN sourceTerm TO source_term');
        $this->addSql('ALTER TABLE search_synonym RENAME COLUMN targetTerms TO target_terms');
        $this->addSql('ALTER TABLE search_synonym RENAME COLUMN createdAt TO created_at');
        $this->addSql('ALTER TABLE search_synonym RENAME COLUMN updatedAt TO updated_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE search_synonym RENAME COLUMN updated_at TO updatedAt');
        $this->addSql('ALTER TABLE search_synonym RENAME COLUMN created_at TO createdAt');
        $this->addSql('ALTER TABLE search_synonym RENAME COLUMN target_terms TO targetTerms');
        $this->addSql('ALTER TABLE search_synonym RENAME COLUMN source_term TO sourceTerm');

        $this->addSql('ALTER TABLE search_relevance_profile RENAME COLUMN updated_at TO updatedAt');
        $this->addSql('ALTER TABLE search_relevance_profile RENAME COLUMN created_at TO createdAt');
        $this->addSql('ALTER TABLE search_relevance_profile RENAME COLUMN field_weights TO fieldWeights');
        $this->addSql('ALTER TABLE search_relevance_profile RENAME COLUMN resource_type TO resourceType');
        $this->addSql('ALTER TABLE search_relevance_profile RENAME COLUMN name_entity TO nameEntity');

        $this->addSql('DROP INDEX uniq_search_reindex_job_job_key');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CD5AF60D4E453838 ON search_reindex_job (job_key)');

        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN updated_at TO updatedAt');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN created_at TO createdAt');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN error_message TO errorMessage');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN error_class TO errorClass');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN duration_ms TO durationMs');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN denied_count TO deniedCount');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN returned_total TO returnedTotal');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN provider_total TO providerTotal');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN provider_name TO providerName');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN user_id TO userId');
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN query_text TO queryText');

        $this->addSql('ALTER TABLE search_indexed_resource RENAME COLUMN updated_at TO updatedAt');
        $this->addSql('ALTER TABLE search_indexed_resource RENAME COLUMN created_at TO createdAt');

        $this->addSql('ALTER TABLE search_index RENAME COLUMN updated_at TO updatedAt');
        $this->addSql('ALTER TABLE search_index RENAME COLUMN created_at TO createdAt');
        $this->addSql('ALTER TABLE search_index RENAME COLUMN name_entity TO nameEntity');
    }
}
