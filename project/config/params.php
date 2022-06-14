<?php

declare(strict_types=1);

use Psr\Log\LogLevel;
use Spiral\Database\Driver\MySQL\MySQLDriver;
use Viktorprogger\YiisoftInform\Infrastructure\Console\CacheClearCommand;
use Viktorprogger\YiisoftInform\Infrastructure\Queue\RealtimeEventHandler;
use Viktorprogger\YiisoftInform\Infrastructure\Queue\RealtimeEventMessage;
use Viktorprogger\YiisoftInform\Infrastructure\Telegram\Action\HelloAction;
use Viktorprogger\YiisoftInform\Infrastructure\Telegram\Action\RealtimeAction;
use Viktorprogger\YiisoftInform\Infrastructure\Telegram\Action\RealtimeEditAction;
use Viktorprogger\YiisoftInform\Infrastructure\Telegram\Action\SummaryAction;
use Viktorprogger\YiisoftInform\Infrastructure\Telegram\Action\SummaryEditAction;
use Viktorprogger\YiisoftInform\SubDomain\GitHub\Infrastructure\Console\LoadEventsCommand;
use Viktorprogger\YiisoftInform\SubDomain\GitHub\Infrastructure\Console\LoadRepositoriesCommand;
use Yiisoft\Yii\Cycle\Schema\Conveyor\CompositeSchemaConveyor;

return [
    'telegram routes' => [],

    'yiisoft/aliases' => [
        'aliases' => [
            '@root' => dirname(__DIR__),
            '@runtime' => '@root/runtime',
            '@vendor' => '@root/vendor'
        ],
    ],
    'yiisoft/yii-console' => [
        'commands' => [],
    ],
    'yiisoft/yii-cycle' => [
        // DBAL config
        'dbal' => [
            // SQL query logger. Definition of Psr\Log\LoggerInterface
            'query-logger' => 'loggerCycle',
            // Default database
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'default']
            ],
            'connections' => [
                'default' => [
                    'driver' => MySQLDriver::class,
                    'connection' => 'mysql:dbname=' . getenv('DB_NAME') . ';host=db',
                    'username' => getenv('DB_LOGIN'),
                    'password' => getenv('DB_PASSWORD'),
                ],
            ],
        ],

        // Cycle migration config
        'migrations' => [
            'directory' => '@root/migrations',
            'namespace' => 'Viktorprogger\\Template\\Migration', // TODO Change this
            'table' => 'migration',
            'safe' => false,
        ],

        /**
         * Annotated/attributed entity directories list.
         * {@see \Yiisoft\Aliases\Aliases} are also supported.
         */
        'entity-paths' => [
            '@root/src',
            '@vendor/viktorprogger/telegram-bot/src/Infrastructure/Entity' // TODO Remove if not needed
        ],

        /**
         * Config for {@see \Yiisoft\Yii\Cycle\Factory\OrmFactory}
         * Null, classname or {@see PromiseFactoryInterface} object.
         *
         * @link https://github.com/cycle/docs/blob/master/advanced/promise.md
         */
        'orm-promise-factory' => null,
        'conveyor' => CompositeSchemaConveyor::class,
    ],
];
