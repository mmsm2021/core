<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210527121458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'enable "citext" extension if postgresql';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            stristr(get_class($this->platform), 'postgre') === false,
            'Skipped due to not being PostgreSQL'
        );
        $this->addSql('CREATE EXTENSION IF NOT EXISTS citext;');
    }

    public function down(Schema $schema): void
    {
        $this->skipIf(
            stristr(get_class($this->platform), 'postgre') === false,
            'Skipped due to not being PostgreSQL'
        );
        $this->addSql('DROP EXTENSION IF EXISTS citext;');
    }
}
