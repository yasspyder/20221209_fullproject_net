<?php

namespace GeekBrains\LevelTwo\Blog\Commands\FakeDate;

use Faker\Generator;
use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Post;
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\LevelTwo\Blog\User;
use GeekBrains\LevelTwo\Blog\UUID;
use GeekBrains\LevelTwo\Person\Name;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;

class PopulateDB extends Command
{

	public function __construct(
		private Generator $faker,
		private UsersRepositoryInterface $usersRepository,
		private PostsRepositoryInterface $postsRepository,
		private CommentsRepositoryInterface $commentsRepository,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setName('fake-data:populate-db')
			->setDescription('Populates DB with fake data')

			->addOption(
				'users-number',
				null, // при вызове shortcut создает бесконечно юзеров
				InputOption::VALUE_OPTIONAL,
				'Set quantity of creating users',
				10
			)
			->addOption(
				'posts-number',
				null, // при вызове shortcut создает бесконечно посты
				InputOption::VALUE_OPTIONAL,
				'Set quantity of creating posts',
				20
			)
			->addOption(
				'comments-number',
				null,
				InputOption::VALUE_OPTIONAL,
				'Set quantity of creating comments for every post',
				1
			);
	}

	/**
	 * @throws InvalidArgumentException
	 */
	protected function execute(
		InputInterface $input,
		OutputInterface $output
	): int
	{

		$users = [];
		$usersNumber = $input->getOption('users-number');

		for ($i = 0; $i < $usersNumber; $i++) {
			$user = $this->createFakeUser();
			$users[] = $user;
			$output->writeln("Creat user: " . $user->username());
		}

		$posts = [];
		$postsNumber = $input->getOption('posts-number');

		foreach ($users as $user) {
			for ($i = 0; $i < $postsNumber; $i++) {
				$post = $this->createFakePost($user);
				$posts[] = $post;
				$output->writeln('Create post: ' . $post->getPostHeader());
			}
		}

		$commentsNumber = $input->getOption('comments-number');

		foreach ($posts as $post) {
			for ($i = 0; $i < $commentsNumber; $i++) {

				$user = $this->createFakeUser();
				$output->writeln(
					"Creat user for comment: "
					. $user->username()
				);

				$this->createFakeComment($post, $user);
				$output->writeln(
					'Create comment from: '
					. $user->username()
					. ' for post: '
					. $post->getPostHeader()
				);
			}
		}

		return Command::SUCCESS;

	}

	/**
	 * @throws InvalidArgumentException
	 */
	private function createFakeComment(Post $post, User $author): Comment
	{
		$comment = new Comment(
			UUID::random(),
			$author,
			$post,
			$this->faker->realText(100)
		);

		$this->commentsRepository->save($comment);
		return $comment;
	}

	/**
	 * @throws InvalidArgumentException
	 */
	private function createFakeUser(): User
	{
		$user = User::creatFrom(
			new Name(
				$this->faker->firstName,
				$this->faker->lastName
			),
			$this->faker->unique()->userName,
			$this->faker->password
		);

		$this->usersRepository->save($user);

		return $user;
	}

	/**
	 * @throws InvalidArgumentException
	 */
	private function createFakePost(User $author): Post
	{
		$post = new Post(
			UUID::random(),
			$author,
			$this->faker->sentence(6, true),
			$this->faker->realText
		);

		$this->postsRepository->save($post);
		return $post;
	}


}