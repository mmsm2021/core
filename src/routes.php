<?php

use App\Actions\Location\DeleteAction as DeleteLocation;
use App\Actions\Location\GetAction as GetLocation;
use App\Actions\Location\ListAction as ListLocations;
use App\Actions\Location\PatchAction as UpdateLocation;
use App\Actions\Location\PostAction as CreateLocation;
use App\Actions\Country\GetAction as GetCountry;
use App\Actions\Country\ListAction as ListCountries;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteCollectorProxy;

/**
 * @OA\Info(title="CoreAPI", version="1.0.0")
 */

/**
 * @OA\Schema(
 *   schema="uuid",
 *   type="string",
 *   format="uuid",
 *   description="Universally unique identifier 128-bits"
 * )
 */

/**
 * @OA\Schema(
 *   schema="jwt",
 *   type="string",
 *   format="jwt",
 *   description="A JSON Web Token",
 *   default="Bearer {id-token}"
 * )
 */

/**
 * @OA\Schema(
 *     schema="FreeForm",
 *     type="object",
 *     description="Key-value Pairs in a JSON Object.",
 *     @OA\AdditionalProperties(type="string"),
 * )
 */

/**
 * @OA\Schema(
 *   schema="timestamp",
 *   type="string",
 *   format="timestamp",
 *   description="ISO-8806 timestamp format in PHP: Y-m-d\TH:i:sO"
 * )
 */

/**
 * @OA\Schema(
 *   schema="error",
 *   type="object",
 *   @OA\Property(property="error", type="boolean"),
 *   @OA\Property(
 *     property="message",
 *     type="array",
 *     @OA\Items(type="string")
 *   )
 * )
 */

/**
 * @OA\Schema(
 *     schema="LocationList",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/Location")
 * )
 */

/**
 * @OA\Schema(
 *     schema="CountryList",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/Country")
 * )
 */

/** @var \Slim\App $app */
/** @var \Psr\Container\ContainerInterface $container */
$app->addRoutingMiddleware();
$app->add($container->get(\Slim\Middleware\ErrorMiddleware::class));
$app->add($container->get(\MMSM\Lib\AuthorizationMiddleware::class));
$app->add($container->get(\Slim\Middleware\BodyParsingMiddleware::class));

$app->options('{routes:.+}', function (ResponseFactory $responseFactory) {
    return $responseFactory->createResponse(204);
});

$app->group('/api/v1', function(RouteCollectorProxy $group) {
    $group->get('/locations', ListLocations::class);
    $group->get('/locations/{id}', GetLocation::class);
    $group->post('/locations', CreateLocation::class);
    $group->delete('/locations/{id}', DeleteLocation::class);
    $group->patch('/locations/{id}', UpdateLocation::class);

    // countries
    $group->get('/countries/{iso3}', GetCountry::class);
    $group->get('/countries', ListCountries::class);
});