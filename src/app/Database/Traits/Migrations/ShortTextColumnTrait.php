<?php

namespace App\Database\Traits\Migrations;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Schema\Column;

trait ShortTextColumnTrait
{
    /**
     * @param string $name
     * @param Table $table
     * @param string|null $uniqueIndexName
     * @return Column
     */
    protected function makeShortTextColumn(string $name, Table $table, ?string $uniqueIndexName = null)
    {
        $column = $table->addColumn($name, Types::STRING);
        $column->setLength(200);
        $column->setNotnull(true);
        if ($uniqueIndexName !== null) {
            $table->addUniqueIndex([$name], $uniqueIndexName);
        }
        return $column;
    }
}