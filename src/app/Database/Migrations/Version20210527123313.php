<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Repositories\CountryRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20210527123313 extends AbstractMigration
{

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Creates "' . CountryRepository::TABLE_NAME . '" table.';
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema): void
    {
        $table = $schema->createTable(CountryRepository::TABLE_NAME);
        $table->addColumn('iso3', Types::STRING)
            ->setNotnull(true)
            ->setLength(4);

        $table->setPrimaryKey(['iso3']);

        $table->addColumn('name', Types::STRING)
            ->setNotnull(true)
            ->setLength(255);
        $table->addUniqueIndex(['name'], 'UNIQUE_COUNTRY_NAME');
    }

    /**
     * @inheritDoc
     */
    public function down(Schema $schema): void
    {
        if ($schema->hasTable(CountryRepository::TABLE_NAME)) {
            $schema->dropTable(CountryRepository::TABLE_NAME);
        }
    }
}
