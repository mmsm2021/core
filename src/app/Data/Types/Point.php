<?php

namespace App\Data\Types;

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

/**
 * Class Point
 * @package App\Data\Types
 * @OA\Schema(
 *   schema="Point",
 *   type="object",
 *   description="Point object",
 * )
 */
class Point
{
    /**
     * @var float
     * @OA\Property(
     *     property="latitude",
     *     type="string",
     *     format="number",
     *     description="Latitude",
     *     default="0"
     * )
     */
    private float $latitude;

    /**
     * @var float
     * @OA\Property(
     *     property="longitude",
     *     type="string",
     *     format="number",
     *     description="Longitude",
     *     default="0"
     * )
     */
    private float $longitude;

    /**
     * Point constructor.
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @return float[]
     */
    public function toArray(): array
    {
        return [
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
        ];
    }

    /**
     * @param array $data
     * @return Point
     * @throws ValidationException
     */
    public static function fromArray(array $data): Point
    {
        v::arrayType()
            ->notEmpty()
            ->key('latitude', v::numericVal(), true)
            ->key('longitude', v::numericVal(), true)
            ->check($data);
        return new self(floatval($data['latitude']), floatval($data['longitude']));
    }
}