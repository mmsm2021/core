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
    'log.dir' => string('{root.dir}/var/log'),
    'environment' => env('ENV', 'development'),
    'log.errors' => env('LOG_ERRORS', false),
    'log.error.details' => env('LOG_ERROR_DETAILS', false),
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
    BodyParsingMiddleware::class => function(JsonBodyParser $jsonBodyParser, XmlBodyParser $xmlBodyParser) {
        return new BodyParsingMiddleware([
            'application/json' => $jsonBodyParser,
            'application/xml' => $xmlBodyParser,
        ]);
    },
    ResponseFactoryInterface::class => create(ResponseFactory::class),
    Logger::class => function(ContainerInterface $container) {
        if (!file_exists($container->get('log.dir'))) {
            mkdir($container->get('log.dir'), 0777, true);
        }
        return new Logger(
            'default',
            [
                new StreamHandler(
                    $container->get('log.dir') . '/debug.log',
                    Logger::DEBUG,
                    true,
                    0777,
                    true
                ),
                new StreamHandler(
                    $container->get('log.dir') . '/info.log',
                    Logger::INFO,
                    true,
                    0777,
                    true
                ),
                new StreamHandler(
                    $container->get('log.dir') . '/notice.log',
                    Logger::NOTICE,
                    true,
                    0777,
                    true
                ),
                new StreamHandler(
                    $container->get('log.dir') . '/warning.log',
                    Logger::WARNING,
                    true,
                    0777,
                    true
                ),
                new StreamHandler(
                    $container->get('log.dir') . '/error.log',
                    Logger::ERROR,
                    true,
                    0777,
                    true
                ),
                new StreamHandler(
                    $container->get('log.dir') . '/critical.log',
                    Logger::CRITICAL,
                    true,
                    0777,
                    true
                ),
                new StreamHandler(
                    $container->get('log.dir') . '/alert.log',
                    Logger::ALERT,
                    true,
                    0777,
                    true
                ),
                new StreamHandler(
                    $container->get('log.dir') . '/emergency.log',
                    Logger::EMERGENCY,
                    true,
                    0777,
                    true
                ),
            ],
            [],
            new DateTimeZone('UTC')
        );
    },
    LoggerInterface::class => get(Logger::class),
    ErrorMiddleware::class => function(
        ContainerInterface $container,
        JsonErrorHandler $jsonErrorHandler,
        ValidationExceptionJsonHandler $validationExceptionJsonHandler,
        LoggerInterface $logger,
        App $app
    ) : ErrorMiddleware {
        $env = $container->get('environment');
        $le = $container->get('log.errors');
        $led = $container->get('log.error.details');
        $isDevelopment = (stristr($env, 'dev') !== false);
        $errors = ($le === true || $le == 'true' || $isDevelopment);
        $errorDetails = ($led === true || $led == 'true' || $isDevelopment);
        $errorMiddleware = new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            $isDevelopment,
            $errors,
            $errorDetails,
            $logger
        );
        $errorMiddleware->setDefaultErrorHandler($jsonErrorHandler);
        $errorMiddleware->setErrorHandler(
            ValidationException::class,
            $validationExceptionJsonHandler,
            true
        );
        return $errorMiddleware;
    },
];