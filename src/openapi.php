<?php

require_once __DIR__ . '/vendor/autoload.php';

$openapi = OpenApi\Generator::scan(OpenApi\Util::finder(__DIR__, [
    __DIR__ . '/vendor'
]));

echo $openapi->toJson();
