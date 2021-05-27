<?php

use App\Actions\Location\DeleteAction;
use App\Actions\Location\GetAction;
use App\Actions\Location\ListAction;
use App\Actions\Location\PostAction;
use Slim\Routing\RouteCollectorProxy;

/** @var \Slim\App $app */
/** @var \Psr\Container\ContainerInterface $container */
$app->addRoutingMiddleware();
$app->add($container->get(\Slim\Middleware\ErrorMiddleware::class));
$app->add($container->get(\MMSM\Lib\AuthorizationMiddleware::class));
$app->add($container->get(\Slim\Middleware\BodyParsingMiddleware::class));

$app->group('/api/v1', function(RouteCollectorProxy $group) {
    $group->get('/locations', ListAction::class);
    $group->get('/locations/{id}', GetAction::class);
    $group->post('/locations', PostAction::class);
    $group->delete('/locations/{id}', DeleteAction::class);
});