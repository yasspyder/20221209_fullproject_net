<?php

namespace GeekBrains\LevelTwo\Blog\Commands\Post;

use GeekBrains\LevelTwo\Blog\Exceptions\PostNotFoundException;
use GeekBrains\LevelTwo\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use GeekBrains\LevelTwo\Blog\UUID;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use GeekBrains\LevelTwo\Blog\Exceptions\InvalidArgumentException;

class DeletePostCommand extends Command
{
	public function __construct(
		private PostsRepositoryInterface $postsRepository
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setName("posts:delete")
			->setDescription("Deletes a post")

			->addArgument(
				"uuid",
				InputArgument::REQUIRED,
				"UUID of a post to delete"
			)
			->addOption(
				"check-existence",
				"c",
				InputOption::VALUE_NONE,
				"Check if post actually exist"
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
		$question = new ConfirmationQuestion(
			"Delete post [Y/n]: ",
			false
		);

		if (!$this->getHelper('question')
			->ask($input, $output, $question)) {
			return Command::SUCCESS;
		}

		$uuid = new UUID( $input->getArgument('uuid'));

		if ($input->getOption('check-existence')) {
			try {
				$this->postsRepository->get($uuid);
			} catch (PostNotFoundException $exception) {
				$output->writeln($exception->getMessage());
				return Command::FAILURE;
			}
		}

		$this->postsRepository->delete($uuid);

		$output->writeln("Post $uuid was deleted");

		return Command::SUCCESS;

	}
}