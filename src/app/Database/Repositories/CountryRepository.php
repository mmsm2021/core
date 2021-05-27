<?php

namespace App\Database\Repositories;

use App\Database\Entities\Country;
use App\Database\RepositoryInterface;
use App\Exceptions\NoSuchEntityException;
use Doctrine\ORM\EntityManager;

class CountryRepository implements RepositoryInterface
{
    public const TABLE_NAME = 'countries';
    public const ENTITY = Country::class;

    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * CountryRepository constructor.
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

    /**
     * @param string $iso3
     * @return Country
     * @throws NoSuchEntityException
     */
    public function getByIso3(string $iso3): Country
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT u FROM ' . Country::class . ' u WHERE u.iso3 = ?1');
        $query->setParameter(1, $iso3);
        $result = $query->getResult();
        if (empty($result)) {
            throw new NoSuchEntityException('Failed to find a country by iso3 "' . $iso3 . '".');
        }
        return $result[array_keys($result)[0]];
    }

    /**
     * @param string $name
     * @return Country
     * @throws NoSuchEntityException
     */
    public function getByName(string $name): Country
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT u FROM ' . Country::class . ' u WHERE u.name = ?1');
        $query->setParameter(1, $name);
        $result = $query->getResult();
        if (empty($result)) {
            throw new NoSuchEntityException('Failed to find a country by name "' . $name . '".');
        }
        return $result[array_keys($result)[0]];
    }
}