<?php

declare(strict_types=1);

namespace App\Searching\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260920204000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename Searching query-log tenant identity to canonical vendor_id.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN tenantId TO vendor_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE search_query_log RENAME COLUMN vendor_id TO tenantId');
    }
}
