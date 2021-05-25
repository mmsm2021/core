<?php

namespace App\Database\Repositories;

use App\Database\Entities\Location;
use App\Database\Entities\Log;
use App\Database\RepositoryInterface;
use App\Database\Traits\Repository\TimestampPropertyTrait;
use App\Exceptions\NoSuchEntityException;
use App\Exceptions\SaveException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

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

    /**
     * @param string $id
     * @param bool $includeDeleted
     * @return Location
     * @throws NoSuchEntityException
     */
    public function getById(string $id, bool $includeDeleted = false): Location
    {
        $dql = 'SELECT u FROM ' . Location::class . ' u WHERE u.id = ?1';
        if (!$includeDeleted) {
            $dql .= ' AND u.deletedAt IS NULL';
        }
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter(1, $id);
        $result = $query->getResult();
        if (empty($result)) {
            throw new NoSuchEntityException('Failed to find a location by id "' . $id . '".');
        }
        return $result[array_keys($result)[0]];
    }

    /**
     * @param string $id
     * @param bool $includeDeleted
     * @return bool
     */
    public function idExists(string $id, bool $includeDeleted = false): bool
    {
        try {
            $this->getById($id, $includeDeleted);
            return true;
        } catch (NoSuchEntityException $exception) {
            return false;
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isNameUnique(string $name): bool
    {
        $query = $this->getEntityManager()->createQuery(
            'SELECT u FROM ' . Location::class . ' u WHERE u.name = ?1'
        );
        $query->setParameter(1, $name);
        $result = $query->getResult();
        return empty($result);
    }

    /**
     * @param Location $location
     * @return Location
     * @throws SaveException
     */
    public function save(Location $location): Location
    {
        try {
            if ($this->idExists($location->getId(), true)) {
                $this->markEntityAsUpdated($location);
            }
            $this->persist($location);
            return $location;
        } catch (\Throwable $throwable) {
            if ($throwable instanceof SaveException) {
                throw $throwable;
            } else {
                throw new SaveException('Failed to save Location.', $throwable->getCode(), $throwable);
            }
        }
    }

    /**
     * @param Location $location
     * @throws \App\Exceptions\IncompatibleTraitException
     * @throws \ReflectionException
     */
    protected function markEntityAsUpdated(Location $location): void
    {
        $this->getUpdatedProperty()->setAccessible(true);
        $this->getUpdatedProperty()->setValue($location, new \DateTimeImmutable('now'));
        $this->getUpdatedProperty()->setAccessible(false);
    }

    /**
     * @param Location $location
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function persist(Location $location)
    {
        $this->getEntityManager()->persist($location);
        $this->getEntityManager()->flush();
    }
}