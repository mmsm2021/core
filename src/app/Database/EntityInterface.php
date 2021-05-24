<?php

namespace App\Database;

use Doctrine\ORM\Mapping\ClassMetadata;

interface EntityInterface
{
    /**
     * Defines the metadata for the entity.
     * @param ClassMetadata $metadata
     * @return void
     */
    public static function loadMetadata(ClassMetadata $metadata);
}