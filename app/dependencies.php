<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
// use mysqli;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            file_put_contents(__DIR__ . "/../logs/system.log", date("[D, d M Y H:i:s]") . " " .
            'loggerSettings-> ' . json_encode($loggerSettings) . " " .
            "file:" . __DIR__ . '/' . basename(__FILE__) . "\r\n", FILE_APPEND);
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        // mysqli::class => function (ContainerInterface $c) {
        //     $settings = $c->get(SettingsInterface::class);
        //     $dbSettings = $settings->get('database');
        //     file_put_contents(__DIR__ . "/../logs/system.log", date("[D, d M Y H:i:s]") . " " .
        //     'dbSettings-> ' . json_encode($dbSettings) . " " .
        //     "file:" . __DIR__ . '/' . basename(__FILE__) . "\r\n", FILE_APPEND);
        //     $host = $dbSettings['host'];
        //     $database = $dbSettings['database'];
        //     $user = $dbSettings['user'];
        //     $pass = $dbSettings['pass'];
        //     return new mysqli($host, $user, $pass, $database);
        // }
    ]);
};
