<?php

use App\Database\Types\PointType;
use App\Exceptions\DefinitionException;
use MMSM\Lib\AuthorizationMiddleware;
use MMSM\Lib\ErrorHandlers\JsonErrorHandler;
use MMSM\Lib\ErrorHandlers\ValidationExceptionJsonHandler;
use MMSM\Lib\Parsers\JsonBodyParser;
use MMSM\Lib\Parsers\XmlBodyParser;
use MMSM\Lib\Validators\JWKValidator;
use MMSM\Lib\Validators\JWTValidator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Psr\Log\LoggerInterface;
use Respect\Validation\Exceptions\ValidationException;
use Slim\App;
use Slim\Middleware\BodyParsingMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\ResponseFactory;
use Doctrine\DBAL\Types\Type;

use function DI\env;
use function DI\create;
use function DI\get;
use function DI\string;

return [
    'root.dir' => __DIR__,
    'database.connection.url' => env('DB_URI'),
    'database.entity.paths' => [
        __DIR__ . '/app/Database/Entities/',
    ],
    'database.proxies.dir' => __DIR__ . '/cache/Database/Proxies',
    'database.proxies.namespace' => 'Database\Proxies',
    'database.migrations.config' => [
        'table_storage' => [
            'table_name' => 'doctrine_migration_versions',
            'version_column_name' => 'version',
            'version_column_length' => 1024,
            'executed_at_column_name' => 'executed_at',
            'execution_time_column_name' => 'execution_time',
        ],

        'migrations_paths' => [
            'App\Database\Migrations' => __DIR__ . '/app/Database/Migrations',
        ],

        'all_or_nothing' => true,
        'check_database_platform' => true,
        'organize_migrations' => 'none',
    ],
    MappingDriver::class => function(ContainerInterface $container){
        return new StaticPHPDriver($container->get('database.entity.paths'));
    },
    Configuration::class => function(ContainerInterface $container, MappingDriver $mappingDriver){
        $appMode = $container->get('environment');
        $config = new Configuration();
        $config->setMetadataDriverImpl($mappingDriver);
        $config->setProxyDir($container->get('database.proxies.dir'));
        $config->setProxyNamespace($container->get('database.proxies.namespace'));

        if(str_contains($appMode, "dev")){
            $config->setAutoGenerateProxyClasses(true);
        }else{
            $config->setAutoGenerateProxyClasses(false);
        }
        return $config;
    },
    EntityManager::class => function(ContainerInterface $container, Configuration $configuration) {
        $em = EntityManager::create([
            'url' => $container->get('database.connection.url')
        ], $configuration);
        if (!Type::hasType(PointType::POINT)) {
            Type::addType(PointType::POINT, PointType::class);
        }
        $em->getConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('point', 'point');
        return $em;
    },
];