<?php

declare(strict_types=1);

use Cycle\Database\Config\MySQL\DsnConnectionConfig;
use Cycle\Database\Config\MySQLDriverConfig;
use Yiisoft\Yii\Cycle\Schema\Conveyor\CompositeSchemaConveyor;

return [
    'yiisoft/yii-cycle' => [
        // DBAL config
        'dbal' => [
            // SQL query logger. Definition of Psr\Log\LoggerInterface
            'query-logger' => 'loggerCycle',
            // Default database
            'default' => 'default',
            'aliases' => [],
            'databases' => [
                'default' => ['connection' => 'default'],
            ],
            'connections' => [
                'default' => new MySQLDriverConfig(
                    connection: new DsnConnectionConfig( // TODO Put env variables to the root .env files
                        dsn:      'mysql:dbname=' . getenv('DB_NAME') . ';host=db',
                        user:     getenv('DB_LOGIN'),
                        password: getenv('DB_PASSWORD'),
                    ),
                ),
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
            '@vendor/viktorprogger/telegram-bot/src/Infrastructure/Entity',
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
