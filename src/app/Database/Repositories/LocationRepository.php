<?php

namespace App\Database\Repositories;

use App\Database\Entities\Location;
use App\Database\RepositoryInterface;
use App\Database\Traits\Repository\TimestampPropertyTrait;
use Doctrine\ORM\EntityManager;

class LocationRepository implements RepositoryInterface
{
    use TimestampPropertyTrait;

    public const TABLE_NAME = 'locations';
    public const ENTITY = Location::class;

    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * LocationRepository constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @inheritDoc
     */
    public function getEntityClassFQN(): string
    {
        return static::ENTITY;
    }

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return static::TABLE_NAME;
    }
}