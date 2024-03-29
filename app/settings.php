<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

	// Global Settings Object
	$containerBuilder->addDefinitions([
		SettingsInterface::class => function () {
			return new Settings([
				'displayErrorDetails' => true, // Should be set to false in production
				'logError'            => true,
				'logErrorDetails'     => true,
				'logger' => [
					'name' => 'slim-app',
					'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
					'level' => Logger::DEBUG,
				],
				'database' => [
					'host' => $_ENV['HOST_DB'],
					'database' => $_ENV['DATABASE'],
					'user' => $_ENV['USER_DB'],
					'pass' => $_ENV['PASS_DB']
				],
			]);
		}
	]);
};
