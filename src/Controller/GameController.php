<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use App\Model\GameStatus;
use App\Model\GameTurn;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game_index')]
    public function index(GameRepository $gameRepository): Response
    {
        $waitingGames = $gameRepository->findBy(['status' => GameStatus::WAITING]);
        $activeGames = $gameRepository->findBy(['status' => GameStatus::PLAYING]);
        return $this->render('game/index.html.twig', [
            'waitingGames' => $waitingGames,
            'activeGames' => $activeGames
        ]);
    }

    #[Route('/game/new', name: 'app_game_new')]
    public function new(EntityManagerInterface $entityManager): Response
    {
        $game = new Game();
        $game->setStatus(GameStatus::WAITING);
        $game->setCreatedAt(new \DateTimeImmutable());
        $entityManager->persist($game);
        $entityManager->flush();

        return $this->redirectToRoute('app_game_play', ['id' => $game->getId()]);
    }

    #[Route('/game/{id}', name: 'app_game_play')]
    public function play(Game $game): Response
    {
        return $this->render('game/play.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/game/{id}/join', name: 'app_game_join')]
    public function join(Game $game, EntityManagerInterface $entityManager): Response
    {
        if ($game->getStatus() !== GameStatus::WAITING) {
            throw new \Exception("Game status is not waiting");
        }
        $game->setStatus(GameStatus::PLAYING);
        $entityManager->persist($game);
        $entityManager->flush();

        return $this->redirectToRoute('app_game_play', ['id' => $game->getId()]);
    }

    #[Route('/game/{id}/move/{cell}', name: 'app_game_move')]
    public function move(Game $game, int $cell, EntityManagerInterface $entityManager): Response
    {
        if ($game->getStatus() !== GameStatus::PLAYING) {
            throw new \Exception("Game status is not playing");
        }
        $board = $game->getBoard();

        $board[$cell] = $game->getCurrentTurn();
        $game->setBoard($board);

        $winner = $this->checkWinner($board);
        if ($winner) {
            $game->setWinner($winner);
        } else {
            $game->setCurrentTurn(match ($game->getCurrentTurn()) {
                GameTurn::X_TURN => GameTurn::O_TURN,
                GameTurn::O_TURN => GameTurn::X_TURN,
            });
        }

        $entityManager->flush();

        return $this->redirectToRoute('app_game_play', ['id' => $game->getId()]);
    }

    #[Route('/game/{id}/leave', name: 'app_game_leave')]
    public function leave(): Response
    {
        #TODO finish the game
        return new Response();
    }

    private function checkWinner(array $board): ?GameTurn
    {
        $lines = [
            [0, 1, 2], [3, 4, 5], [6, 7, 8],    // Rows
            [0, 3, 6], [1, 4, 7], [2, 5, 8],    // Columns
            [0, 4, 8], [2, 4, 6],               // Diagonals
        ];

        /*
         *                  0,  1,  2
         *                  3,  4,  5
         *                  6,  7,  8
         *                  0,  3,  6
         *                  1,  4,  7
         *                  2,  5,  8
         *                  0,  4,  8
         *                  2,  4,  6
         */
        foreach ($lines as [$a, $b, $c]) {
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
}
