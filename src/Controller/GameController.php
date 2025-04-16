<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use App\Model\GameStatus;
use App\Repository\GameRepository;
use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game_index')]
    public function index(GameRepository $gameRepository): Response
    {
        $availableGames = $gameRepository->findBy(['status' => GameStatus::WAITING]);
        $activeGames = $gameRepository->findBy(['status' => GameStatus::PLAYING]);
        return $this->render('game/index.html.twig', [
            'availableGames' => $availableGames,
            'activeGames' => $activeGames
        ]);
    }

    #[Route('/game/new', name: 'app_game_new')]
    #[IsGranted('ROLE_USER')]
    public function new(
        GameService $gameService,
    ): Response
    {
        $game = $gameService->createNewGame($this->getUser());
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
    #[IsGranted('ROLE_USER')]
    public function join(
        Game        $game,
        GameService $gameService,
    ): Response
    {
        $gameService->joinGame($game, $this->getUser());
        return $this->redirectToRoute('app_game_play', ['id' => $game->getId()]);
    }

    #[Route('/game/{id}/move/{cell}', name: 'app_game_move')]
    #[IsGranted('ROLE_USER')]
    public function move(
        Game        $game,
        int         $cell,
        GameService $gameService
    ): Response
    {
        if ($game->getStatus() !== GameStatus::PLAYING) {
            throw new \Exception("Game status is not playing");
        }
        if (!$game->isCurrentPlayer($this->getUser())) {
            throw new \Exception("Not your turn!");
        }

        $gameService->makeMove($game, $cell);

        return $this->redirectToRoute('app_game_play', ['id' => $game->getId()]);
    }
}
