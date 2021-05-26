<?php

namespace App\Database\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
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
        $format = (stristr($platform->getName(), 'postgre') !== false ? '(%f,%f)' : 'POINT(%f %f)');
        list($longitude, $latitude) = sscanf($value, $format);

        return new Point($latitude, $longitude);
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (stristr($platform->getName(), 'postgre') !== false && $value instanceof Point) {
            return sprintf('(%F,%F)', $value->getLongitude(), $value->getLatitude());
        }

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
        if (stristr(get_class($platform), 'postgre') !== false) {
            return $sqlExpr;
        } else {
            return sprintf('AsText(%s)', $sqlExpr);
        }
    }

    /**
     * @inheritDoc
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        if (stristr(get_class($platform), 'postgre') !== false) {
            return $sqlExpr;
        } else {
            return sprintf('PointFromText(%s)', $sqlExpr);
        }
    }
}