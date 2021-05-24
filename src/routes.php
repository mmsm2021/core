<?php

use App\Actions\Location\GetAction;
use App\Actions\Location\ListAction;
use Slim\Routing\RouteCollectorProxy;

/** @var \Slim\App $app */
/** @var \Psr\Container\ContainerInterface $container */
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$authMiddleware = $container->get(\MMSM\Lib\AuthorizationMiddleware::class);
$bodyMiddleware = $container->get(\Slim\Middleware\BodyParsingMiddleware::class);

$app->group('/api/v1', function(RouteCollectorProxy $group) use ($authMiddleware, $bodyMiddleware) {
    $group->get('/location', ListAction::class);
    $group->get('/location/{id}', GetAction::class);
});