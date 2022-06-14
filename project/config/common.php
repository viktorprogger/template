<?php
/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramClientInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Application;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareDispatcher;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Router;
use Viktorprogger\TelegramBot\Infrastructure\Client\TelegramClientLog;
use Viktorprogger\TelegramBot\Infrastructure\Client\TelegramClientSymfony;
use Viktorprogger\TelegramBot\Infrastructure\UpdateRuntime\Middleware\RequestPersistingMiddleware;
use Viktorprogger\TelegramBot\Infrastructure\UpdateRuntime\Middleware\RouterMiddleware;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\Apcu\ApcuCache;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Definitions\Reference;
use Yiisoft\Injector\Injector;

/** @var array $params */

return [
    TelegramClientInterface::class => TelegramClientSymfony::class,
    TelegramClientSymfony::class => [
        '__construct()' => [
            'token' => getenv('BOT_TOKEN'),
            'logger' => Reference::to('loggerTelegram'),
        ],
    ],
    TelegramClientLog::class => ['__construct()' => ['logger' => Reference::to('loggerTelegram')]],
    HttpClientInterface::class => static fn() => HttpClient::create(),
    UuidFactoryInterface::class => UuidFactory::class,
    LoggerInterface::class => Logger::class,
    Logger::class => static function(Aliases $alias) {
        return (new Logger('application'))
            ->pushProcessor(static function (array $record): array {
                if (isset($record['extra'])) {
                    $record['context']['extra'] = $record['extra'] ?? [];
                    unset($record['extra']);
                }

                return $record;
            })
            ->pushProcessor(new PsrLogMessageProcessor())
            ->pushProcessor(new MemoryUsageProcessor())
            ->pushProcessor(new MemoryPeakUsageProcessor())
            ->pushProcessor(new IntrospectionProcessor())
            ->pushHandler(
                (new RotatingFileHandler($alias->get('@runtime/logs/app.log')))
                    ->setFilenameFormat('{filename}-{date}', RotatingFileHandler::FILE_PER_MONTH)
                    ->setFormatter(new JsonFormatter())
            );
    },
    'loggerTelegram' => static fn(Logger $logger) => $logger->withName('telegram'),
    'loggerGithub' => static fn(Logger $logger) => $logger->withName('github'),
    'loggerCycle' => static fn(Logger $logger) => $logger->withName('cycle'),
    CacheInterface::class => ApcuCache::class,
    Router::class => [
        '__construct()' => ['routes' => $params['telegram routes']]
    ],
    Application::class => [
        '__construct()' => [
            'fallbackHandler' => Reference::to(NotFoundRequestHandler::class), // FIXME Add this handler to the bot package
            'dispatcher' => DynamicReference::to(static function (Injector $injector): MiddlewareDispatcher {
                return ($injector->make(MiddlewareDispatcher::class))
                    ->withMiddlewares(
                        [
                            RequestPersistingMiddleware::class,
                            RouterMiddleware::class,
                        ]
                    );
            }),
        ],
    ],
];
