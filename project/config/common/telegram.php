<?php

declare(strict_types=1);

use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramClientInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Application;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareDispatcher;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Router;
use Viktorprogger\TelegramBot\Infrastructure\Client\TelegramClientLog;
use Viktorprogger\TelegramBot\Infrastructure\Client\TelegramClientSymfony;
use Viktorprogger\TelegramBot\Infrastructure\UpdateRuntime\Handler\RouteNotFoundRequestHandler;
use Viktorprogger\TelegramBot\Infrastructure\UpdateRuntime\Middleware\RequestPersistingMiddleware;
use Viktorprogger\TelegramBot\Infrastructure\UpdateRuntime\Middleware\RouterMiddleware;
use Yiisoft\Definitions\DynamicReference;
use Yiisoft\Definitions\Reference;
use Yiisoft\Injector\Injector;

/**
 * @var array $params
 */

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
    'loggerTelegram' => static fn(Logger $logger) => $logger->withName('telegram'),
    Router::class => [
        '__construct()' => ['routes' => $params['telegram routes']]
    ],
    Application::class => [
        '__construct()' => [
            'fallbackHandler' => new RouteNotFoundRequestHandler(),
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
