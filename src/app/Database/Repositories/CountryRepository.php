<?php

namespace App\Database\Repositories;

use App\Database\Entities\Country;
use App\Database\RepositoryInterface;

class CountryRepository implements RepositoryInterface
{
    public const TABLE_NAME = 'countries';
    public const ENTITY = Country::class;

    public function getEntityClassFQN(): string
    {
        return static::ENTITY;
    }

    public function getTableName(): string
    {
        return static::TABLE_NAME;
    }
}