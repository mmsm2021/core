<?php

namespace App\Data\Validator;

use Respect\Validation\Validator as v;

class LocationValidator implements ValidatorInterface
{

    /**
     * @inheritDoc
     */
    public function check($data): bool
    {
        v::arrayType()
            ->notEmpty()
            ->key('name', v::stringType()->notEmpty()->length(4, 200), true)
            ->key('point', v::arrayType()->notEmpty()
                ->key('latitude', v::stringType()->numericVal()->notEmpty(), true)
                ->key('longitude', v::stringType()->numericVal()->notEmpty(), true), true)
            ->key('metadata', v::arrayType())->check($data);
        return true;
    }
}