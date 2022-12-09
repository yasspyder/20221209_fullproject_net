<?php

namespace GeekBrains\PHPUnit\Container;

use GeekBrains\LevelTwo\Blog\Container\DIContainer;
use GeekBrains\LevelTwo\Blog\Exceptions\NotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\InMemoryUsersRepository;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DIContainerTest extends TestCase
{
	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function testItThrowsAnExceptionIfCannotResolveType(): void
	{
		$container = new DIContainer();

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage(
			"Cannot resolve type: GeekBrains\PHPUnit\Container\SomeClass"
		);

		$container->get(SomeClass::class);

	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function testItResolvesClassWithoutDependencies(): void
	{
		$container = new DIContainer();

		$object = $container->get(SomeClassWithoutDependencies::class);

		$this->assertInstanceOf(
			SomeClassWithoutDependencies::class,
			$object
		);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function testItResolversClassByContract(): void
	{
		$container = new DIContainer();

		$container->bind(
			UsersRepositoryInterface::class,
			InMemoryUsersRepository::class
		);

		$object = $container->get(UsersRepositoryInterface::class);

		$this->assertInstanceOf(
			InMemoryUsersRepository::class, $object
		);
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function testItReturnsPredefinedObject(): void
	{
		$container = new DIContainer();

		$container->bind(
			SomeClassWithParameter::class,
			new SomeClassWithParameter(10)
		);

		$object = $container->get(SomeClassWithParameter::class);

		$this->assertInstanceOf(
			SomeClassWithParameter::class,
			$object
		);

		$this->assertSame(10, $object->value());

	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function testItReturnsClassWithDependencies(): void
	{
		$container = new DIContainer();

		$container->bind(
			SomeClassWithParameter::class,
			new SomeClassWithParameter(10)
		);

		$object = $container->get(ClassDependingOnAnother::class);

		$this->assertInstanceOf(
			ClassDependingOnAnother::class,
			$object
		);

	}

}