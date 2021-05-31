<?php

namespace App\Database\Repositories;

use App\Database\Entities\Country;
use App\Database\RepositoryInterface;
use App\Exceptions\EntityNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\QueryException;

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
     * @throws EntityNotFoundException
     */
    public function getByIso3(string $iso3): Country
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT u FROM ' . Country::class . ' u WHERE u.iso3 = ?1');
        $query->setParameter(1, $iso3);
        $result = $query->getResult();
        if (empty($result)) {
            throw new EntityNotFoundException('Failed to find a country by iso3 "' . $iso3 . '".');
        }
        return $result[array_keys($result)[0]];
    }

    /**
     * @param string $name
     * @return Country
     * @throws EntityNotFoundException
     */
    public function getByName(string $name): Country
    {
        $query = $this->getEntityManager()
            ->createQuery('SELECT u FROM ' . Country::class . ' u WHERE u.name = ?1');
        $query->setParameter(1, $name);
        $result = $query->getResult();
        if (empty($result)) {
            throw new EntityNotFoundException('Failed to find a country by name "' . $name . '".');
        }
        return $result[array_keys($result)[0]];
    }

    /**
     * @param Criteria|null $criteria
     * @param bool $asArrays
     * @return ArrayCollection
     */
    public function getList(?Criteria $criteria = null, bool $asArrays = false): ArrayCollection
    {
        $collection = new ArrayCollection;
        try {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('u')
                ->from(Country::class, 'u');
            if ($criteria !== null) {
                $qb->addCriteria($criteria);
            }
            $result = $qb->getQuery()->getResult();
            if (!is_array($result)) {
                $result = [];
            }
            foreach ($result as $item) {
                /** @var Country $item */
                $collection->add(($asArrays ? $item->toArray() : $item));
            }
            return $collection;
        } catch (QueryException $exception) {
            return $collection;
        }
    }
}