<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Game;
use App\Entity\User;
use App\Factory\GameFactory;
use App\Factory\UserFactory;
use App\Model\GameStatus;
use App\Model\GameTurn;
use App\Repository\GameRepository;
use App\Service\GameService;
use App\Service\MercureService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GameService::class)]
class GameServiceTest extends TestCase
{
    private User $userX;
    private User $userO;
    private GameRepository $gameRepository;
    private MercureService $mercureService;

    protected function setUp(): void
    {
        parent::setUp();
        $userFactory = new UserFactory();
        $this->userX = $userFactory->create('player1@example.com', 'password');
        $this->userO = $userFactory->create('player2@example.com', 'password');
        $this->gameRepository = $this->createMock(GameRepository::class);
        $this->mercureService = $this->createMock(MercureService::class);
    }

    public function testCreateNewGame(): void
    {
        $game = $this->createMock(Game::class);

        $gameFactory = $this->createMock(GameFactory::class);
        $gameFactory->expects($this->once())
            ->method('create')
            ->with($this->userX)
            ->willReturn($game);

        $gameRepository = $this->createMock(GameRepository::class);
        $gameRepository->expects($this->once())
            ->method('save')
            ->with($game, true);

        $mercureService = $this->createMock(MercureService::class);
        $mercureService->expects($this->once())
            ->method('publishNewGameAvailable')
            ->with($game);

        $service = new GameService($mercureService, $gameFactory, $gameRepository);

        $result = $service->createNewGame($this->userX);

        $this->assertSame($game, $result);
        $this->assertInstanceOf(Game::class, $game);
    }

    public function testJoinGameWorkingProperly()
    {
        $factory = new GameFactory();
        $game = $factory->create($this->userX);

        $mercureService = $this->createMock(MercureService::class);
        $mercureService->expects($this->once())
            ->method('publishGameStarted')
            ->with($game);

        $gameFactory = $this->createMock(GameFactory::class);

        $gameRepository = $this->createMock(GameRepository::class);
        $gameRepository->expects($this->once())
            ->method('save')
            ->with($game, true);

        $service = new GameService($mercureService, $gameFactory, $gameRepository);
        $result = $service->joinGame($game, $this->userO);
        $this->assertSame(GameStatus::PLAYING, $result->getStatus());
        $this->assertInstanceOf(User::class, $result->getUserO());
    }

    public function testMakeMoveUpdatesCorrectCell()
    {
        $factory = new GameFactory();
        $game = $factory->create($this->userX);
        $emptyBoard = $game->getBoard();
        for ($i = 0; $i < 9; $i++) {
            $this->assertEmpty($emptyBoard[$i]);
        }

        $service = new GameService($this->mercureService, $factory, $this->gameRepository);
        $resultGame = $service->makeMove($game, 5);
        $board = $resultGame->getBoard();
        $cell = $board[5];
        $this->assertInstanceOf(GameTurn::class, $cell);
        $this->assertSame(GameTurn::X_TURN, $cell);
    }

    public function testMakeMoveReturnsWinnerIfBoardHasWinningCombination()
    {
        $factory = new GameFactory();
        $game = $factory->create($this->userX);

        $service = new GameService($this->mercureService, $factory, $this->gameRepository);

        $service->makeMove($game, 0); // X
        $service->makeMove($game, 3); // O
        $service->makeMove($game, 1); // X
        $service->makeMove($game, 4); // O
        $service->makeMove($game, 2); // X wins
        $this->assertInstanceOf(GameTurn::class, $game->getWinner());
        $this->assertSame(GameTurn::X_TURN, $game->getWinner());
    }

    public function testMakeMoveSwitchCurrentTurnToAnotherPlayer()
    {
        $factory = new GameFactory();
        $game = $factory->create($this->userX);
        $service = new GameService($this->mercureService, $factory, $this->gameRepository);
        $service->makeMove($game, 0);
        $this->assertSame(GameTurn::O_TURN, $game->getCurrentTurn());
    }

    public function testMakeMoveWithWrongCellNumberThrowsException()
    {
        $factory = new GameFactory();
        $game = $factory->create($this->userX);
        $service = new GameService($this->mercureService, $factory, $this->gameRepository);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cell');
        $service->makeMove($game, 99);
    }

    public function testMakeMoveWithTakenCellNumberThrowsException()
    {
        $factory = new GameFactory();
        $game = $factory->create($this->userX);
        $service = new GameService($this->mercureService, $factory, $this->gameRepository);
        $service->makeMove($game, 0);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This cell is already occupied');
        $service->makeMove($game, 0);
    }

    public function testMakeMoveThrowsIfGameIsAlreadyWon()
    {
        $factory = new GameFactory();
        $game = $factory->create($this->userX);
        $game->setWinner(GameTurn::X_TURN);

        $service = new GameService($this->mercureService, $factory, $this->gameRepository);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Game is already finished');

        $service->makeMove($game, 4);
    }
    public function testMakeMoveDetectsDraw()
    {
        $factory = new GameFactory();
        $game = $factory->create($this->userX);
        $service = new GameService($this->mercureService, $factory, $this->gameRepository);

        $moves = [0,1,2,4,3,5,7,6,8];
        foreach ($moves as $move) {
            $service->makeMove($game, $move);
        }

        $this->assertNull($game->getWinner());
        $this->assertSame(GameStatus::FINISHED, $game->getStatus());
    }
}
