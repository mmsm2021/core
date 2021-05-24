<?php

namespace App\Database\Traits\Migrations;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Schema\Column;

trait IdColumnTrait
{
    /**
     * @param string $name
     * @param Table $table
     * @param bool $primary
     * @return Column
     */
    protected function makeIdColumn(string $name, Table $table, bool $primary = false): Column
    {
        $column = $table->addColumn($name, Types::STRING);
        $column->setLength(40);
        $column->setNotnull(true);
        if ($primary) {
            $table->setPrimaryKey([$name]);
        }
        return $column;
    }
}