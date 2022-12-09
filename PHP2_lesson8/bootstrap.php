<?php

use GeekBrains\LevelTwo\Blog\Container\DIContainer;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\{
	UsersRepositoryInterface,
	SqliteUsersRepository
};
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\{
	PostsRepositoryInterface,
	SqlitePostsRepository
};
use GeekBrains\LevelTwo\Blog\Repositories\LikesRepository\{
	LikesRepositoryInterface,
	SqliteLikesRepository,
	CommentLikesRepositoryInterface,
	SqliteCommentLikesRepository,

};
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\{
	CommentsRepositoryInterface,
	SqliteCommentsRepository,
};
use GeekBrains\LevelTwo\Blog\Repositories\AuthTokensRepository\{
	AuthTokensRepositoryInterface,
	SqliteAuthTokensRepository,
};
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;
use GeekBrains\LevelTwo\HTTP\Auth\{
	IdentificationInterface,
	JsonBodyUuidIdentification,
	PasswordAuthentication,
	PasswordAuthenticationInterface,
	TokenAuthenticationInterface,
	BearerTokenAuthentication
};
use Faker\Provider\Lorem;
use Faker\Provider\ru_RU\Internet;
use Faker\Provider\ru_RU\Person;
use Faker\Provider\ru_RU\Text;

require_once __DIR__ . '/vendor/autoload.php';

Dotenv::createImmutable(__DIR__)->safeLoad();

$container = new DIContainer();

$logger = new Logger('blog');
if ($_SERVER['LOG_TO_FILE'] === 'yes') {
	$logger
		->pushHandler(
			new StreamHandler(
				__DIR__.'/logs/blog.log'
			))
		->pushHandler(
			new StreamHandler(
				__DIR__."/logs/blog.error.log",
				level: Logger::ERROR,
				bubble: false
			));
}
if ($_SERVER['LOG_TO_CONSOLE'] === 'yes') {
	$logger
		->pushHandler(
			new StreamHandler("php://stdout")
		);
}

$container->bind(
	TokenAuthenticationInterface::class,
	BearerTokenAuthentication::class
);

$container->bind(
	AuthTokensRepositoryInterface::class,
	SqliteAuthTokensRepository::class
);

$container->bind(
	PasswordAuthenticationInterface::class,
	PasswordAuthentication::class
);

$container->bind(
	IdentificationInterface::class,
	JsonBodyUuidIdentification::class
);

$container->bind(
	LoggerInterface::class,
	$logger
);

$container->bind(
	PDO::class,
	new PDO('sqlite:' . __DIR__ . '/' . $_SERVER['SQLITE_DB_PATH'])
);

$container->bind(
	UsersRepositoryInterface::class,
	SqliteUsersRepository::class
);

$container->bind(
	PostsRepositoryInterface::class,
	SqlitePostsRepository::class
);

$container->bind(
	LikesRepositoryInterface::class,
	SqliteLikesRepository::class
);

$container->bind(
	CommentsRepositoryInterface::class,
	SqliteCommentsRepository::class
);

$container->bind(
	CommentLikesRepositoryInterface::class,
	SqliteCommentLikesRepository::class
);

$faker = new \Faker\Generator();

$faker->addProvider(new Person($faker));
$faker->addProvider(new Internet($faker));
$faker->addProvider(new Text($faker));
$faker->addProvider(new Lorem($faker));

$container->bind(
	\Faker\Generator::class,
	$faker
);

return $container;