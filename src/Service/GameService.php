<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\User;
use App\Factory\GameFactory;
use App\Model\GameStatus;
use App\Model\GameTurn;
use App\Repository\GameRepository;

class GameService
{
    private const WINNING_LINES = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8],    // Rows
        [0, 3, 6], [1, 4, 7], [2, 5, 8],    // Columns
        [0, 4, 8], [2, 4, 6],               // Diagonals
    ];

    public function __construct(
        private readonly MercureService $mercureService,
        private readonly GameFactory    $gameFactory,
        private readonly GameRepository $gameRepository,
    )
    {

    }

    public function createNewGame(?User $user): Game
    {
        $newGame = $this->gameFactory->create($user);
        $this->gameRepository->save($newGame, true);
        $this->mercureService->publishNewGameAvailable($newGame);
        return $newGame;
    }

    public function joinGame(Game $game, ?User $user): Game
    {
        $game->setUserO($user);
        $game->setStatus(GameStatus::PLAYING);

        $this->gameRepository->save($game, true);

        $this->mercureService->publishGameUpdate($game, [
            'type' => 'game_start',
            'id' => $game->getId(),
        ]);
        return $game;
    }

    public function makeMove(Game $game, int $cell): Game
    {
        if ($cell < 0 || $cell > 9) {
            throw new \Exception('Invalid cell');
        }
        $board = $game->getBoard();
        if ($board[$cell] instanceof GameTurn) {
            throw new \Exception('This cell is already occupied');
        }

        $board[$cell] = $game->getCurrentTurn();
        $game->setBoard($board);

        $winner = $this->checkWinner($board);
        if ($winner) {
            $game->setWinner($winner);
        } else {
            $game->setCurrentTurn($this->getNextTurn($game));
        }

        $this->mercureService->publishGameUpdate($game, [
            'cell' => $cell,
            'winner' => $winner,
        ]);

        $this->gameRepository->save($game, true);
        return $game;
    }

    private function checkWinner(array $board): ?GameTurn
    {
        foreach (self::WINNING_LINES as [$a, $b, $c]) {
            if (
                $board[$a] instanceof GameTurn &&
                $board[$a] === $board[$b] &&
                $board[$a] === $board[$c]
            ) {
                return $board[$a];
            }
        }
        return null;
    }

    private function getNextTurn(Game $game): ?GameTurn
    {
        return match ($game->getCurrentTurn()) {
            GameTurn::X_TURN => GameTurn::O_TURN,
            GameTurn::O_TURN => GameTurn::X_TURN,
        };
    }
}
