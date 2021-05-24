<?php

use App\Exceptions\DefinitionException;
use MMSM\Lib\AuthorizationMiddleware;
use MMSM\Lib\Parsers\JsonBodyParser;
use MMSM\Lib\Parsers\XmlBodyParser;
use MMSM\Lib\Validators\JWKValidator;
use MMSM\Lib\Validators\JWTValidator;
use Psr\Container\ContainerInterface;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Slim\Middleware\BodyParsingMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Psr7\Factory\ResponseFactory;

use function DI\env;
use function DI\create;

return [
    'environment' => env('ENV', 'development'),
    'auth.jwk_uri' => env('JWK_URI', false),
    'auth.allowedBearers' => [
        'Bearer'
    ],
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
    AuthorizationMiddleware::class => function(
        JWKValidator $JWKValidator,
        JWTValidator $JWTValidator,
        ContainerInterface $container
    ) : AuthorizationMiddleware {
        $authMiddleware = new AuthorizationMiddleware($JWKValidator, $JWTValidator);
        if (stristr($container->get('environment'), 'prod') !== false) {
            $authMiddleware->loadJWKs('/keys/auth0_jwks.json');
        } else {
            if (!is_string($container->get('auth.jwk_uri'))) {
                throw new DefinitionException('invalid type gotten from "auth.jwk_uri".');
            }
            $authMiddleware->loadJWKs($container->get('auth.jwk_uri'), false);
        }
        foreach ($container->get('auth.allowedBearers') as $bearer) {
            $authMiddleware->addAllowedBearer($bearer);
        }
        return $authMiddleware;
    },
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
        return EntityManager::create([
            'url' => $container->get('database.connection.url')
        ], $configuration);
    },
    BodyParsingMiddleware::class => function(JsonBodyParser $jsonBodyParser, XmlBodyParser $xmlBodyParser) {
        return new BodyParsingMiddleware([
            'application/json' => $jsonBodyParser,
            'application/xml' => $xmlBodyParser,
        ]);
    },
    ResponseFactoryInterface::class => create(ResponseFactory::class),
];