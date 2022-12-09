<?php

namespace GeekBrains\PHPUnit\Repositories\CommentsRepository;

use GeekBrains\PHPUnit\DummyLogger;
use GeekBrains\LevelTwo\Blog\{Post, User, UUID};
use GeekBrains\LevelTwo\Blog\Comment;
use GeekBrains\LevelTwo\Blog\Exceptions\{CommentNotFoundException,
	InvalidArgumentException,
	PostNotFoundException,
	UserNotFoundException};
use GeekBrains\LevelTwo\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use GeekBrains\LevelTwo\Person\Name;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

class SqliteCommentsRepositoryTest extends TestCase
{

    public function testItSavesCommentsToDatabase(): void {

        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => '123e4567-e89b-12d3-a456-426614174000',
                ':post_uuid' => '723e4567-e89b-12d3-a456-426614174000',
                ':author_uuid' => '723e4567-e89b-12d3-a456-426614174009',
                ':comment_text' => 'Lorem text',
            ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqliteCommentsRepository(
			$connectionStub,
			new DummyLogger()
		);

        $user = new User(
            new UUID('723e4567-e89b-12d3-a456-426614174009'),
            new Name('first_name', 'last_name'),
            'name',
			'123'
        );

        $post = new Post(
            new UUID('723e4567-e89b-12d3-a456-426614174000'),
            $user,
            'Some title',
            'Some text'
        );

        $repository->save(
            new Comment(
                new UUID('123e4567-e89b-12d3-a456-426614174000'),
                $user,
                $post,
                'Lorem text'
            )
        );
    }

    /**
     * @throws PostNotFoundException
     * @throws UserNotFoundException
     * @throws InvalidArgumentException|CommentNotFoundException
     */
    public function testItGetCommentById(): void {

        $connectionStub = $this->createStub(\PDO::class);
        $statementMock = $this->createMock(\PDOStatement::class);

        $statementMock->method('fetch')->willReturn([
            'uuid' => '7b094211-1881-40f4-ac73-365ad0b2b2d4',
            'author_uuid' => '5a91ed7a-0ae4-495f-b666-c52bc8f13fe4',
            'comment_text' => 'Some comment text',
            'post_uuid' => '723e4567-e89b-12d3-a456-426614174000',
            'post_title' => 'Some title',
            'post_text' => 'Some text',
            'username' => 'ivan123',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
			'password' => '123'
        ]);

        $connectionStub->method('prepare')->willReturn($statementMock);

        $commentsRepository = new SqliteCommentsRepository(
			$connectionStub,
			new DummyLogger()
		);
        $comment = $commentsRepository->get(new UUID('7b094211-1881-40f4-ac73-365ad0b2b2d4'));

        $this->assertSame('7b094211-1881-40f4-ac73-365ad0b2b2d4', (string)$comment->uuid());

    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     * @throws CommentNotFoundException
     */
    public function testItThrowsAnExceptionWhenCommentNotFound(): void {

        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);

        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqliteCommentsRepository(
			$connectionStub,
			new DummyLogger()
		);

        $this->expectExceptionMessage('Cannot find comment: d02eef61-1a06-460f-b859-202b84164734');
        $this->expectException(CommentNotFoundException::class);
        $repository->get(new UUID('d02eef61-1a06-460f-b859-202b84164734'));
    }
}