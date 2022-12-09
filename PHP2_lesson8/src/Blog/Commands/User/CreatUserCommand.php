<?php

namespace GeekBrains\LevelTwo\Blog\Commands\User;

use GeekBrains\LevelTwo\Blog\Exceptions\{
	UserNotFoundException,
	InvalidArgumentException,
};
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Person\Name;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreatUserCommand extends Command
{
	public function __construct(
		private UsersRepositoryInterface $usersRepository
	)
	{
		parent::__construct();
	}

	protected function configure()
	{
		$this->setName('users:create')
			->setDescription('Creates new user')

			->addArgument(
				'first_name',
				InputArgument::REQUIRED,
				'User`s first name'
			)
			->addArgument(
				'last_name',
				InputArgument::REQUIRED,
				'User`s last name'
			)
			->addArgument(
				'username',
				InputArgument::REQUIRED,
				'User`s username-login'
			)
			->addArgument(
				'password',
				InputArgument::REQUIRED,
				'User`s password'
			);
	}

	/**
	 * @throws InvalidArgumentException
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('Create user command started');

		$username = $input->getArgument('username');
		if ($this->userExists($username)) {
			$output->writeln("This user is already exist: $username");
			return Command::FAILURE;
		}

		$user = User::creatFrom(
			new Name(
				$input->getArgument('first_name'),
				$input->getArgument('last_name')
			),
			$username,
			$input->getArgument('password')
		);

		$this->usersRepository->save($user);

		$output->writeln("User created: " . $user->uuid());

		return Command::SUCCESS;

	}

	private function userExists(string $username): bool
	{
		try {
			$this->usersRepository->getByUsername($username);
		} catch (UserNotFoundException) {
			return false;
		}

		return true;
	}

}