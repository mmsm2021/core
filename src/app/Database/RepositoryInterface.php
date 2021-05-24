<?php

namespace App\Database;

interface RepositoryInterface
{
    /**
     * returns FQN of the entity class.
     * @return string
     */
    public function getEntityClassFQN(): string;

    /**
     * returns table name of the repository.
     * @return string
     */
    public function getTableName(): string;
}