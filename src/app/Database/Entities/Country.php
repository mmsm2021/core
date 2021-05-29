<?php

namespace App\Database\Entities;

use App\Database\EntityInterface;
use App\Database\Repositories\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Class Country
 * @package App\Database\Entities
 * @OA\Schema(
 *   schema="Country",
 *   type="object",
 *   description="Country object",
 * )
 */
class Country implements EntityInterface
{
    /**
     * @var string
     * @OA\Property()
     */
    private string $iso3;

    /**
     * @var string
     * @OA\Property()
     */
    private string $name;

    /**
     * @var Collection
     */
    private Collection $locations;

    /**
     * Country constructor.
     */
    public function __construct()
    {
        $this->locations = new ArrayCollection;
    }

    /**
     * @return string
     */
    public function getIso3(): string
    {
        return $this->iso3;
    }

    /**
     * @param string $iso3
     */
    public function setIso3(string $iso3): void
    {
        $this->iso3 = $iso3;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Collection
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return [
            'iso3' => $this->getIso3(),
            'name' => $this->getName(),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable(CountryRepository::TABLE_NAME);
        $builder->setCustomRepositoryClass(CountryRepository::class);
        $builder->createField('iso3', Types::STRING)
            ->columnName('iso3')
            ->length(4)
            ->nullable(false)
            ->makePrimaryKey()
            ->build();

        $builder->createField('name', Types::STRING)
            ->length(255)
            ->nullable(false)
            ->build();

        $builder->addUniqueConstraint(['name'], 'UNIQUE_COUNTRY_NAME');

        $builder->createOneToMany('locations', Location::class)
            ->mappedBy('country')
            ->setOrderBy(['name' => 'DESC'])
            ->fetchExtraLazy()
            ->build();
    }
}