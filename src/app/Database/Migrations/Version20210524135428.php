<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Repositories\LocationRepository;
use App\Database\Traits\Migrations\IdColumnTrait;
use App\Database\Traits\Migrations\ShortTextColumnTrait;
use App\Database\Traits\Migrations\TimestampColumnsTrait;
use App\Database\Types\PointType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Schema\Column;

final class Version20210524135428 extends AbstractMigration
{
    use IdColumnTrait, ShortTextColumnTrait, TimestampColumnsTrait;

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Creates "'.LocationRepository::TABLE_NAME.'" table.';
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema): void
    {
        $table = $schema->createTable(LocationRepository::TABLE_NAME);
        $this->makeIdColumn('id', $table, true);
        $this->makeShortTextColumn('name', $table, 'UNIQUE_LOCATION_NAME');
        $this->makePointColumn('point', $table);

        $table->addColumn('metadata', Types::JSON)
            ->setNotnull(false)
            ->setDefault(null);

        $this->addCreatedAt($table);
        $this->addUpdatedAt($table);
        $this->addDeletedAt($table);
    }

    /**
     * @inheritDoc
     */
    public function down(Schema $schema): void
    {
        if ($schema->hasTable(LocationRepository::TABLE_NAME)) {
            $schema->dropTable(LocationRepository::TABLE_NAME);
        }
    }

    /**
     * @param string $name
     * @param Table $table
     * @return Column
     */
    private function makePointColumn(string $name, Table $table): Column
    {
        $column = $table->addColumn($name, PointType::POINT);
        $column->setNotnull(false);
        $column->setDefault(null);
        return $column;
    }
}
