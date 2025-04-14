<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Model\GameTurn;

class GameService
{
    private const WINNING_LINES = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8],    // Rows
        [0, 3, 6], [1, 4, 7], [2, 5, 8],    // Columns
        [0, 4, 8], [2, 4, 6],               // Diagonals
    ];

    public function __construct(private readonly MercureService $mercureService)
    {

    }

    public function makeMove(Game $game, int $cell): bool
    {
        $board = $game->getBoard();

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
        return true;
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
