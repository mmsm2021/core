<?php

namespace App\Database\Traits\Migrations;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Schema\Column;

trait TimestampColumnsTrait
{
    /**
     * @param Table $table
     * @return Column
     */
    public function addCreatedAt(Table $table): Column
    {
        $column = $table->addColumn('created_at', Types::DATETIMETZ_IMMUTABLE);
        $column->setNotnull(true);
        return $column;
    }

    /**
     * @param Table $table
     * @return Column
     */
    public function addUpdatedAt(Table $table): Column
    {
        $column = $table->addColumn('updated_at', Types::DATETIMETZ_IMMUTABLE);
        $column->setNotnull(false);
        $column->setDefault(null);
        return $column;
    }

    /**
     * @param Table $table
     * @return Column
     */
    public function addDeletedAt(Table $table): Column
    {
        $column = $table->addColumn('deleted_at', Types::DATETIMETZ_IMMUTABLE);
        $column->setNotnull(false);
        $column->setDefault(null);
        return $column;
    }
}