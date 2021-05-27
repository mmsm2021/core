<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Repositories\CountryRepository;
use App\Database\Repositories\LocationRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210527152511 extends AbstractMigration
{
    public const CONSTRAINT_NAME = 'location_to_country_link';

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Creates foreign key between "' . LocationRepository::TABLE_NAME . '" and "' . CountryRepository::TABLE_NAME . '".';
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$schema->hasTable(LocationRepository::TABLE_NAME),
            'Missing "' . LocationRepository::TABLE_NAME . '" table.'
        );
        $this->abortIf(
            !$schema->hasTable(CountryRepository::TABLE_NAME),
            'Missing "' . CountryRepository::TABLE_NAME . '" table.'
        );
        $localTable = $schema->getTable(LocationRepository::TABLE_NAME);
        $foreignTable = $schema->getTable(CountryRepository::TABLE_NAME);
        $this->abortIf(
            !$localTable->hasColumn('country'),
            'Table: "' . LocationRepository::TABLE_NAME . '" is missing the column: "country".'
        );
        $this->abortIf(
            !$foreignTable->hasColumn('iso3'),
            'Table: "' . CountryRepository::TABLE_NAME . '" is missing the column: "iso3".'
        );
        $localTable->addForeignKeyConstraint(
            $foreignTable,
            ['country'],
            ['iso3'],
            [],
            static::CONSTRAINT_NAME
        );
    }

    /**
     * @inheritDoc
     */
    public function down(Schema $schema): void
    {
        $this->skipIf(
            !$schema->hasTable(LocationRepository::TABLE_NAME),
            'Missing "' . LocationRepository::TABLE_NAME . '" table.'
        );
        $localTable = $schema->getTable(LocationRepository::TABLE_NAME);
        $this->skipIf(
            !$localTable->hasForeignKey(static::CONSTRAINT_NAME),
            'Table: "' . LocationRepository::TABLE_NAME . '" is missing foreign key: "' . static::CONSTRAINT_NAME. '".'
        );
        $localTable->removeForeignKey(static::CONSTRAINT_NAME);
    }
}
