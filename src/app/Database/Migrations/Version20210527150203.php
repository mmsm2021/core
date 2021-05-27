<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Repositories\LocationRepository;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20210527150203 extends AbstractMigration
{
    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Add address fields to "' . LocationRepository::TABLE_NAME . '" table.';
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
        $table = $schema->getTable(LocationRepository::TABLE_NAME);
        $table->addColumn('street', Types::STRING)
            ->setLength(255)
            ->setNotnull(true);
        $table->addColumn('number', Types::STRING)
            ->setLength(20)
            ->setNotnull(true);
        $table->addColumn('zipcode', Types::STRING)
            ->setLength(10)
            ->setNotnull(true);
        $table->addColumn('city', Types::STRING)
            ->setLength(100)
            ->setNotnull(true);
        $table->addColumn('state', Types::STRING)
            ->setLength(255)
            ->setNotnull(false);
        $table->addColumn('country', Types::STRING)
            ->setNotnull(true)
            ->setLength(4);
    }

    /**
     * @inheritDoc
     */
    public function down(Schema $schema): void
    {
        if ($schema->hasTable(LocationRepository::TABLE_NAME)) {
            $table = $schema->getTable(LocationRepository::TABLE_NAME);
            $this->dropColumnIfExists($table, 'country');
            $this->dropColumnIfExists($table, 'state');
            $this->dropColumnIfExists($table, 'city');
            $this->dropColumnIfExists($table, 'zipcode');
            $this->dropColumnIfExists($table, 'number');
            $this->dropColumnIfExists($table, 'street');
        }
    }

    /**
     * @param Table $table
     * @param string $name
     * @return void
     */
    protected function dropColumnIfExists(Table $table, string $name): void
    {
        if ($table->hasColumn($name)) {
            $table->dropColumn($name);
        }
    }
}
