<?php

require_once __DIR__ . '/vendor/autoload.php';

$containerBuilder = new \DI\ContainerBuilder(
    \MMSM\Lib\Container::class
);

$containerBuilder->addDefinitions(__DIR__ . '/definitions.php');
$containerBuilder->addDefinitions(__DIR__ . '/vendor/mmsm/service-lib/definitions.php');

$container = $containerBuilder->build();

return $container->get(\Doctrine\ORM\EntityManager::class)->getConnection();