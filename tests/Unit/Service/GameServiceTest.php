<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Game;
use App\Entity\User;
use App\Factory\GameFactory;
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
    public function testCreateNewGame(): void
    {
        $user = $this->createMock(User::class);
        $game = $this->createMock(Game::class);

        $gameFactory = $this->createMock(GameFactory::class);
        $gameFactory->expects($this->once())
            ->method('create')
            ->with($user)
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

        $result = $service->createNewGame($user);

        $this->assertSame($game, $result);
        $this->assertInstanceOf(Game::class, $game);
    }

    public function testJoinGameWorkingProperly()
    {
        $user = $this->createMock(User::class);
        $factory = new GameFactory();
        $game = $factory->create($user);

        $mercureService = $this->createMock(MercureService::class);
        $mercureService->expects($this->once())
            ->method('publishGameUpdate')
            ->with($game, [
                'type' => 'game_start',
                'id' => null,
            ]);

        $gameFactory = $this->createMock(GameFactory::class);

        $gameRepository = $this->createMock(GameRepository::class);
        $gameRepository->expects($this->once())
            ->method('save')
            ->with($game, true);

        $service = new GameService($mercureService, $gameFactory, $gameRepository);
        $result = $service->joinGame($game, $user);
        $this->assertSame(GameStatus::PLAYING, $result->getStatus());
        $this->assertInstanceOf(User::class, $result->getUserO());
    }

    public function testMakeMoveSetCorrectCell()
    {
        $user = $this->createMock(User::class);
        $gameRepository = $this->createMock(GameRepository::class);
        $mercureService = $this->createMock(MercureService::class);

        $factory = new GameFactory();
        $game = $factory->create($user);
        $emptyBoard = $game->getBoard();
        for($i = 0; $i < 9; $i++) {
            $this->assertEmpty($emptyBoard[$i]);
        }

        $service = new GameService($mercureService, $factory, $gameRepository);
        $resultGame = $service->makeMove($game, 5);
        $board = $resultGame->getBoard();
        $this->assertInstanceOf(GameTurn::class, $board[5]);
    }

    public function testMakeMoveWithZeroToNineReturnsFullBoard()
    {
        $user = $this->createMock(User::class);
        $gameRepository = $this->createMock(GameRepository::class);
        $mercureService = $this->createMock(MercureService::class);

        $factory = new GameFactory();
        $game = $factory->create($user);

        $service = new GameService($mercureService, $factory, $gameRepository);

        for($i = 0; $i < 9; $i++) {
            $service->makeMove($game, $i);
        }

        $board = $game->getBoard();
        for($i = 0; $i < 9; $i++) {
            $cell = $board[$i];
            $this->assertNotEmpty($cell);
            $this->assertInstanceOf(GameTurn::class, $cell);
        }
    }

    public function testMakeMoveReturnsWinnerIfBoardHasWinningCombination()
    {
        $user = $this->createMock(User::class);
        $gameRepository = $this->createMock(GameRepository::class);
        $mercureService = $this->createMock(MercureService::class);

        $factory = new GameFactory();
        $game = $factory->create($user);

        $service = new GameService($mercureService, $factory, $gameRepository);
        $game->setWinner(GameTurn::X_TURN);
        $service->makeMove($game, 0);
        $game->setWinner(GameTurn::X_TURN);
        $service->makeMove($game, 1);
        $game->setWinner(GameTurn::X_TURN);
        $service->makeMove($game, 2);
        $this->assertInstanceOf(GameTurn::class,$game->getWinner());
        $this->assertSame($game->getWinner(), GameTurn::X_TURN);
    }

    public function testMakeMoveSwitchCurrentTurnToAnotherPlayer()
    {
        $user = $this->createMock(User::class);
        $gameRepository = $this->createMock(GameRepository::class);
        $mercureService = $this->createMock(MercureService::class);

        $factory = new GameFactory();
        $game = $factory->create($user);
        $service = new GameService($mercureService, $factory, $gameRepository);
        $game->setWinner(GameTurn::X_TURN);
        $service->makeMove($game, 0);
        $this->assertSame(GameTurn::O_TURN, $game->getCurrentTurn());
    }

    public function testMakeMoveWithWrongCellNumberThrowsException()
    {
        $user = $this->createMock(User::class);
        $gameRepository = $this->createMock(GameRepository::class);
        $mercureService = $this->createMock(MercureService::class);

        $factory = new GameFactory();
        $game = $factory->create($user);
        $service = new GameService($mercureService, $factory, $gameRepository);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cell');
        $service->makeMove($game, 99);
    }

    public function testMakeMoveWithTakenCellNumberThrowsException()
    {
        $user = $this->createMock(User::class);
        $gameRepository = $this->createMock(GameRepository::class);
        $mercureService = $this->createMock(MercureService::class);

        $factory = new GameFactory();
        $game = $factory->create($user);
        $service = new GameService($mercureService, $factory, $gameRepository);
        $service->makeMove($game, 0);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This cell is already occupied');
        $service->makeMove($game, 0);
    }

}