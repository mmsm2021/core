<?php

namespace App\Database\Entities;

use App\Database\EntityInterface;
use App\Database\Repositories\LocationRepository;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;

class Country implements EntityInterface
{

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable(LocationRepository::TABLE_NAME);
        $builder->setCustomRepositoryClass(LocationRepository::class);
    }
}