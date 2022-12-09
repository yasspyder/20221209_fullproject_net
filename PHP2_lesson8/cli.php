<?php

use GeekBrains\LevelTwo\Blog\Exceptions\AppException;
use GeekBrains\LevelTwo\Blog\Commands\{CreateUserConsoleCommand, Arguments};
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use GeekBrains\LevelTwo\Blog\Commands\User\{
	CreatUserCommand,
	UpdateUserCommand,
};
use GeekBrains\LevelTwo\Blog\Commands\Post\DeletePostCommand;
use GeekBrains\LevelTwo\Blog\Commands\FakeDate\{
	PopulateDB,
};

include __DIR__ . "/vendor/autoload.php";
$container = require __DIR__ . '/bootstrap.php';

$logger= $container->get(LoggerInterface::class);

$application = new Application();

$commandsClasses = [
	CreatUserCommand::class,
	DeletePostCommand::class,
	UpdateUserCommand::class,
	PopulateDB::class,
];

foreach ($commandsClasses as $commandsClass) {
	$command = $container->get($commandsClass);
	$application->add($command);
}

try {
	$application->run();
} catch (Exception $exception) {
	$logger->error($exception->getMessage(), ['exception' => $exception]);
	echo $exception->getMessage();
}


// for work CreatUserConsoleCommand:

/*$command = $container->get(CreateUserConsoleCommand::class);

try {
	$command->handle(Arguments::fromArgv($argv));
} catch (AppException $exception) {
	$logger->error($exception->getMessage(), ['exception' => $exception]);
}*/


