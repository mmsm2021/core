<?php

namespace App\Database\Entities;

use App\Data\Types\Point;
use App\Database\EntityInterface;
use App\Database\Repositories\LocationRepository;
use App\Database\Types\PointType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ramsey\Uuid\Uuid;

/**
 * Class Location
 * @package App\Database\Entities
 */
class Location implements EntityInterface
{

    /**
     * @var string
     */
    private string $id;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var Point
     */
    private Point $point;

    /**
     * @var array
     */
    private array $metadata = [];

    /**
     * @var string
     */
    private string $street;

    /**
     * The number on the street and the floor(if any)
     * @var string
     */
    private string $number;

    /**
     * @var string
     */
    private string $zipcode;

    /**
     * @var string
     */
    private string $city;

    /**
     * @var string|null
     */
    private ?string $state = null;

    /**
     * @var Country
     */
    private Country $country;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $createdAt;

    /**
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * @var DateTimeImmutable|null
     */
    private ?DateTimeImmutable $deletedAt = null;

    /**
     * Location constructor.
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
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
     * @return Point
     */
    public function getPoint(): Point
    {
        return $this->point;
    }

    /**
     * @param Point $point
     */
    public function setPoint(Point $point): void
    {
        $this->point = $point;
    }

    /**
     * @return array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }


    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getZipcode(): string
    {
        return $this->zipcode;
    }

    /**
     * @param string $zipcode
     */
    public function setZipcode(string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return Country
     */
    public function getCountry(): Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     */
    public function setCountry(Country $country): void
    {
        $this->country = $country;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'point' => $this->getPoint()->toArray(),
            'metadata' => $this->getMetadata(),
            'createdAt' => $this->getCreatedAt()->format(\DateTimeInterface::ISO8601),
            'updatedAt' => ($this->getUpdatedAt() instanceof DateTimeImmutable ?
            $this->getUpdatedAt()->format(\DateTimeInterface::ISO8601) : null),
            'deletedAt' => ($this->getDeletedAt() instanceof DateTimeImmutable ?
                $this->getDeletedAt()->format(\DateTimeInterface::ISO8601) : null),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable(LocationRepository::TABLE_NAME);
        $builder->setCustomRepositoryClass(LocationRepository::class);

        $builder->createField('id', Types::STRING)
            ->makePrimaryKey()
            ->nullable(false)
            ->length(40)
            ->build();

        $builder->createField('name', Types::STRING)
            ->nullable(false)
            ->length(200)
            ->build();

        $builder->addUniqueConstraint(['name'], 'UNIQUE_LOCATION_NAME');

        $builder->createField('point', PointType::POINT)
            ->nullable(true)
            ->build();

        $builder->createField('metadata', Types::JSON)
            ->nullable(true)
            ->build();

        $builder->createField('street', Types::STRING)
            ->nullable(false)
            ->length(255)
            ->build();

        $builder->createField('number', Types::STRING)
            ->nullable(false)
            ->length(20)
            ->build();

        $builder->createField('zipcode', Types::STRING)
            ->nullable(false)
            ->length(10)
            ->build();

        $builder->createField('city', Types::STRING)
            ->nullable(false)
            ->length(100)
            ->build();

        $builder->createField('state', Types::STRING)
            ->nullable(true)
            ->length(255)
            ->build();

        $builder->createManyToOne('country', Country::class)
            ->inversedBy('locations')
            ->addJoinColumn('country', 'iso3', false)
            ->fetchEager()
            ->build();

        $builder->createField('createdAt', Types::DATETIMETZ_IMMUTABLE)
            ->nullable(false)
            ->columnName('created_at')
            ->build();

        $builder->createField('updatedAt', Types::DATETIMETZ_IMMUTABLE)
            ->nullable(true)
            ->columnName('updated_at')
            ->build();

        $builder->createField('deletedAt', Types::DATETIMETZ_IMMUTABLE)
            ->nullable(true)
            ->columnName('deleted_at')
            ->build();
    }
}