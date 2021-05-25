<?php

namespace App\Data\Validator;

use Respect\Validation\Exceptions\ValidationException;

interface ValidatorInterface
{
    /**
     * @param $data
     * @return bool
     * @throws ValidationException
     */
    public function check($data): bool;
}