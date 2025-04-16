<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Game;
use App\Entity\User;
use App\Model\GameStatus;
use App\Model\GameTurn;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Game::class)]
class GameTest extends TestCase
{
    public function testCanGetAndSetData(): void
    {
        $game = new Game();
        $game->setUserX((new User())->setEmail('test1@test.com'));
        $game->setUserO((new User())->setEmail('test2@test.com'));
        $game->setCurrentTurn(GameTurn::X_TURN);
        $game->setStatus(GameStatus::WAITING);
        $game->setCreatedAt(new \DateTimeImmutable());

        $this->assertSame('test1@test.com', $game->getUserX()->getEmail());
        $this->assertSame('test2@test.com', $game->getUserO()->getEmail());
        $this->assertSame(GameTurn::X_TURN, $game->getCurrentTurn());
        $this->assertSame(GameStatus::WAITING, $game->getStatus());
    }

    public function testGameHasCorrectNumberOfCells(): void
    {
        $game = new Game();
        $this->assertIsArray($game->getBoard());
        $this->assertCount(9, $game->getBoard());
    }

    public function testCellsCreatedAreEmpty(): void
    {
        $game = new Game();
        foreach ($game->getBoard() as $cell) {
            $this->assertEmpty($cell);
        }
    }

    public function testWinnerIsNullWhenGameCreated(): void
    {
        $game = new Game();
        $this->assertNull($game->getWinner());
    }
}
