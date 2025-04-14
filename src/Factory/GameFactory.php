<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Game;
use App\Entity\User;
use App\Model\GameStatus;
use App\Model\GameTurn;

class GameFactory
{
    public function create(?User $user): Game
    {
        $game = new Game();
        $game->setUserX($user);
        $game->setCurrentTurn(GameTurn::X_TURN);
        $game->setStatus(GameStatus::WAITING);
        $game->setCreatedAt(new \DateTimeImmutable());
        return $game;
    }
}
