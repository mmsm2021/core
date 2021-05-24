<?php

namespace App\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use App\Data\Types\Point;

class PointType extends Type
{
    public const POINT = 'point';

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::POINT;
    }

    /**
     * @inheritDoc
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return 'POINT';
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        list($longitude, $latitude) = sscanf($value, 'POINT(%f %f)');

        return new Point($latitude, $longitude);
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof Point) {
            $value = sprintf('POINT(%F %F)', $value->getLongitude(), $value->getLatitude());
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function canRequireSQLConversion()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function convertToPHPValueSQL($sqlExpr, $platform)
    {
        return sprintf('AsText(%s)', $sqlExpr);
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        return sprintf('PointFromText(%s)', $sqlExpr);
    }
}