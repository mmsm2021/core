<?php

namespace App\Data\Validator;

use Respect\Validation\Validator as v;

class LocationValidator
{

    /**
     * @OA\Schema(
     *     schema="CreateLocationDTO",
     *     type="object",
     *     required={"name","point","metadata","street","number","zipcode","city","country"},
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         minLength=4,
     *         maxLength=200,
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="point",
     *         ref="#/components/schemas/Point",
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="metadata",
     *         ref="#/components/schemas/FreeForm",
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="street",
     *         type="string",
     *         description="The name of the street",
     *         minLength=2,
     *         maxLength=254,
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="number",
     *         type="string",
     *         description="The number on the street and the floor if any",
     *         minLength=1,
     *         maxLength=20,
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="zipcode",
     *         type="string",
     *         minLength=1,
     *         maxLength=10,
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="city",
     *         type="string",
     *         minLength=2,
     *         maxLength=100,
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="string",
     *         minLength=2,
     *         maxLength=254,
     *         nullable=true,
     *         default=null
     *     ),
     *     @OA\Property(
     *         property="country",
     *         type="string",
     *         description="The ISO-3 code of the country.",
     *         minLength=3,
     *         maxLength=3,
     *         nullable=false
     *     )
     * )
     * @param array $data
     * @return bool
     */
    public function postCheck(array $data): bool
    {
        v::arrayType()
            ->notEmpty()
            ->key('name', v::stringType()->notEmpty()->length(4, 200), true)
            ->key('point', v::arrayType()->notEmpty()
                ->key('latitude', v::stringType()->numericVal()->notEmpty(), true)
                ->key('longitude', v::stringType()->numericVal()->notEmpty(), true), true)
            ->key('metadata', v::arrayType())
            ->key('street', v::stringType()->notEmpty()->length(2,254), true)
            ->key('number', v::stringType()->notEmpty()->length(1,20), true)
            ->key('zipcode', v::oneOf(
                v::stringType()->notEmpty()->length(1,10),
                v::intType()->notEmpty()
            ), true)
            ->key('city', v::stringType()->notEmpty()->length(2,100), true)
            ->key('state', v::oneOf(
                v::stringType()->notEmpty()->length(2,254),
                v::nullType()
            ), false)
            ->key('country', v::oneOf(
                v::stringType()->notEmpty(),
                v::arrayType()->notEmpty()
                    ->key('iso3', v::stringType()->notEmpty(), true)
                    ->key('name', v::stringType()->notEmpty(), true)
            ), true)
            ->check($data);
        return true;
    }

    /**
     * @OA\Schema(
     *     schema="UpdateLocationDTO",
     *     type="object",
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         minLength=4,
     *         maxLength=200,
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="point",
     *         ref="#/components/schemas/Point",
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="metadata",
     *         ref="#/components/schemas/FreeForm",
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="street",
     *         type="string",
     *         description="The name of the street",
     *         minLength=2,
     *         maxLength=254,
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="number",
     *         type="string",
     *         description="The number on the street and the floor if any",
     *         minLength=1,
     *         maxLength=20,
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="zipcode",
     *         type="string",
     *         minLength=1,
     *         maxLength=10,
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="city",
     *         type="string",
     *         minLength=2,
     *         maxLength=100,
     *         nullable=false
     *     ),
     *     @OA\Property(
     *         property="state",
     *         type="string",
     *         minLength=2,
     *         maxLength=254,
     *         nullable=true,
     *         default=null
     *     ),
     *     @OA\Property(
     *         property="country",
     *         type="string",
     *         description="The ISO-3 code of the country.",
     *         minLength=3,
     *         maxLength=3,
     *         nullable=false
     *     )
     * )
     * @param array $data
     * @return bool
     */
    public function patchCheck(array $data): bool
    {
        v::arrayType()->notEmpty()->anyOf(
            v::key('name', v::stringType()->notEmpty()->length(4, 200), true),
            v::key('point', v::arrayType()->notEmpty()
                ->key('latitude', v::stringType()->numericVal()->notEmpty(), true)
                ->key('longitude', v::stringType()->numericVal()->notEmpty(), true), true),
            v::key('metadata', v::arrayType()),
            v::key('street', v::stringType()->notEmpty()->length(2,254), true),
            v::key('number', v::stringType()->notEmpty()->length(1,20), true),
            v::key('zipcode', v::oneOf(
                v::stringType()->notEmpty()->length(1,10),
                v::intType()->notEmpty()
            ), true),
            v::key('city', v::stringType()->notEmpty()->length(2,100), true),
            v::key('state', v::oneOf(
                v::stringType()->notEmpty()->length(2,254),
                v::nullType()
            ), true),
            v::key('country', v::oneOf(
                v::stringType()->notEmpty(),
                v::arrayType()->notEmpty()
                    ->key('iso3', v::stringType()->notEmpty(), true)
                    ->key('name', v::stringType()->notEmpty(), true)
            ), true))
            ->check($data);
        return true;
    }
}