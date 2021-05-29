<?php

namespace App\Data\Validator;

use Respect\Validation\Validator as v;

class LocationValidator
{

    /**
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
            ->key('state', v::stringType()->notEmpty()->length(2,254), false)
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