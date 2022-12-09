<?php

use GeekBrains\LevelTwo\HTTP\Actions\Comments\CreateComment;
use GeekBrains\LevelTwo\HTTP\Actions\Users\CreateUser;
use GeekBrains\LevelTwo\HTTP\Actions\Likes\{CreateLike, CreateCommentLike};
use GeekBrains\LevelTwo\HTTP\Actions\Posts\{CreatePost, DeletePost};
use GeekBrains\LevelTwo\HTTP\Actions\Users\FindByUsername;
use GeekBrains\LevelTwo\HTTP\Request;
use GeekBrains\LevelTwo\Blog\Exceptions\{HttpException, JsonException};
use GeekBrains\LevelTwo\HTTP\ErrorResponse;
use GeekBrains\LevelTwo\HTTP\Actions\Auth\{LogIn, LogOut};
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/vendor/autoload.php';

$container = require __DIR__ . '/bootstrap.php';

$request = new Request(
	$_GET,
	$_SERVER,
	file_get_contents('php://input'),
);

$logger = $container->get(LoggerInterface::class);

try {
	$path = $request->path();
} catch (HttpException $exception) {
	$logger->warning($exception->getMessage());
	(new ErrorResponse)->send();
	return;
}

try {
	$method = $request->method();
} catch (HttpException $exception) {
	$logger->warning($exception->getMessage());
	(new ErrorResponse)->send();
	return;
}

$routes = [
	'GET' => [
		'/users/show' => FindByUsername::class,
	],
	'POST' => [
		'/users/create' => CreateUser::class,
		'/posts/create' => CreatePost::class,
		'/posts/comment' => CreateComment::class,
		'/posts/like' => CreateLike::class,
		'/comments/like' => CreateCommentLike::class,
		'/login' => LogIn::class,
		'/logout' => LogOut::class,
	],

	'DELETE' => [
		'/posts' => DeletePost::class,
	]

];

if (!array_key_exists($method, $routes)
	|| !array_key_exists($path, $routes[$method])) {
	$message = "Route not found $method $path";
	(new ErrorResponse($message))->send();
	return;
}

$actionClassName = $routes[$method][$path];

try {
	$action = $container->get($actionClassName);
	$response = $action->handle($request);
	$response->send();
} catch (Exception $exception) {
	$logger->error($exception->getMessage(), ['exception' => $exception]);
	(new ErrorResponse)->send();
}

//$response->send();

