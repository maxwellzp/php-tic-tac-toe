<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Game;
use App\Entity\User;
use App\Factory\GameFactory;
use App\Factory\UserFactory;
use App\Model\GameStatus;
use App\Model\GameTurn;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Service\GameService;
use App\Service\MercureService;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(GameService::class)]
class GameServiceTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private GameRepository $gameRepository;
    private GameFactory $gameFactory;
    private User $userX;
    private User $userO;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $container = static::getContainer();

        $this->gameRepository = $container->get(GameRepository::class);
        $this->gameFactory = $container->get(GameFactory::class);

        $userRepository = $container->get(UserRepository::class);
        $userFactory = $container->get(UserFactory::class);
        $this->userX = $userFactory->create('player1@example.com', 'password');
        $userRepository->save($this->userX);
        $this->userO = $userFactory->create('player2@example.com', 'password');
        $userRepository->save($this->userO);
    }

    public function testCreateNewGamePersistsGame()
    {
        $gameService = $this->createGameServiceWithMockMercure([
            'publishNewGameAvailable' => 1
        ]);

        $newGame = $gameService->createNewGame($this->userX);
        $gameFromDb = $this->gameRepository->find($newGame->getId());

        $this->assertNotNull($gameFromDb->getId());
        $this->assertSame($this->userX->getId(), $gameFromDb->getUserX()->getId());
    }

    public function testJoinGameSetUseroAndUpdateStatusInDatabase()
    {
        $gameService = $this->createGameServiceWithMockMercure([
            'publishNewGameAvailable' => 1,
            'publishGameStarted' => 1
        ]);

        $newGame = $gameService->createNewGame($this->userX);

        $gameService->joinGame($newGame, $this->userO);

        $gameFromDb = $this->gameRepository->find($newGame->getId());

        $this->assertSame(GameStatus::PLAYING, $gameFromDb->getStatus());
        $this->assertSame($gameFromDb->getUserO()->getId(), $this->userO->getId());
    }

    public function testMakeMove()
    {
        $gameService = $this->createGameServiceWithMockMercure([
            'publishNewGameAvailable' => 1,
            'publishGameStarted' => 1,
            'publishGameUpdate' => 1
        ]);

        $newGame = $gameService->createNewGame($this->userX);

        $gameService->joinGame($newGame, $this->userO);

        $gameService->makeMove($newGame, 0);

        $gameFromDb = $this->gameRepository->find($newGame->getId());
        $board = $gameFromDb->getBoard();


        $this->assertSame(GameTurn::X_TURN, $board[0]);
        $this->assertSame(GameTurn::O_TURN, $gameFromDb->getCurrentTurn());
        $this->assertNull($gameFromDb->getWinner());
    }

    public function testMakeMoveThrowsOnInvalidCell()
    {
        $gameService = $this->createGameServiceWithMockMercure([
            'publishNewGameAvailable' => 1,
        ]);

        $game = $gameService->createNewGame($this->userX);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid cell');

        $gameService->makeMove($game, 10);
    }

    public function testMakeMoveThrowsWhenCellIsAlreadyTaken()
    {
        $gameService = $this->createGameServiceWithMockMercure([
            'publishNewGameAvailable' => 1,
            'publishGameStarted' => 1,
            'publishGameUpdate' => 1,
        ]);

        $game = $gameService->createNewGame($this->userX);
        $gameService->joinGame($game, $this->userO);

        $gameService->makeMove($game, 0);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This cell is already occupied');

        $gameService->makeMove($game, 0);
    }

    public function testMakeMoveSetsWinnerWhenWinningConditionIsMet()
    {
        $gameService = $this->createGameServiceWithMockMercure([
            'publishNewGameAvailable' => 1,
            'publishGameStarted' => 1,
            'publishGameUpdate' => 5,
        ]);

        $game = $gameService->createNewGame($this->userX);
        $gameService->joinGame($game, $this->userO);

        $gameService->makeMove($game, 0); // X
        $gameService->makeMove($game, 3); // O
        $gameService->makeMove($game, 1); // X
        $gameService->makeMove($game, 4); // O
        $gameService->makeMove($game, 2); // X wins

        $gameFromDb = $this->gameRepository->find($game->getId());

        $this->assertEquals($this->userX->getId(), $gameFromDb->getUserX()->getId());
        $this->assertSame(GameTurn::X_TURN, $gameFromDb->getWinner());
    }

    public function testJoinGameThrowsWhenUserIsUserX()
    {
        $gameService = $this->createGameServiceWithMockMercure([
            'publishNewGameAvailable' => 1,
        ]);

        $game = $gameService->createNewGame($this->userX);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("You can't join this game because you've already joined the game.");

        $gameService->joinGame($game, $this->userX);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        self::ensureKernelShutdown();
    }

    private function createGameServiceWithMockMercure(array $expects = []): GameService
    {
        $mock = $this->createMock(MercureService::class);

        foreach ($expects as $method => $times) {
            $mock->expects($this->exactly($times))->method($method);
        }
        return new GameService($mock, $this->gameFactory, $this->gameRepository);
    }
}
