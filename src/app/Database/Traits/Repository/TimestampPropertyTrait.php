<?php


namespace App\Database\Traits\Repository;


use App\Database\RepositoryInterface;
use App\Database\Traits\Migrations\TimestampColumnsTrait;
use App\Exceptions\IncompatibleTraitException;
use ReflectionException;
use ReflectionProperty;

trait TimestampPropertyTrait
{

    /**
     * @return ReflectionProperty
     * @throws IncompatibleTraitException
     * @throws ReflectionException
     */
    public function getUpdatedProperty(): ReflectionProperty
    {
        static $updatedProperty;
        if (!($this instanceof RepositoryInterface)) {
            throw new IncompatibleTraitException(
                '"' . static::class . '" is incompatible with trait "' . TimestampColumnsTrait::class . '"'
            );
        }
        if (!isset($updatedProperty)) {
            $updatedProperty = new ReflectionProperty($this->getEntityClassFQN(), 'updatedAt');
        }
        return $updatedProperty;
    }

    /**
     * @return ReflectionProperty
     * @throws IncompatibleTraitException
     * @throws ReflectionException
     */
    public function getDeletedProperty(): ReflectionProperty
    {
        static $deletedProperty;
        if (!($this instanceof RepositoryInterface)) {
            throw new IncompatibleTraitException(
                '"' . static::class . '" is incompatible with trait "' . TimestampColumnsTrait::class . '"'
            );
        }
        if (!isset($deletedProperty)) {
            $deletedProperty = new ReflectionProperty($this->getEntityClassFQN(), 'deletedAt');
        }
        return $deletedProperty;
    }
}