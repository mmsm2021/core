<?php

use App\Actions\Location\DeleteAction;
use App\Actions\Location\GetAction;
use App\Actions\Location\ListAction;
use App\Actions\Location\PatchAction;
use App\Actions\Location\PostAction;
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
 *   format="JWT",
 *   description="A JSON Web Token"
 * )
 */

/**
 * @OA\Schema(
 *     schema="FreeForm",
 *     type="object",
 *     description="Key-value Pairs in a JSON Object.",
 *     additionalProperties=true
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
    $group->get('/locations', ListAction::class);
    $group->get('/locations/{id}', GetAction::class);
    $group->post('/locations', PostAction::class);
    $group->delete('/locations/{id}', DeleteAction::class);
    $group->patch('/locations/{id}', PatchAction::class);
});