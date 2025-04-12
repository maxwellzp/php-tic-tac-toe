<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Game;
use App\Model\GameStatus;
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
        return $this->render('game/index.html.twig', [
            'waitingGames' => $waitingGames,
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

        return new Response();
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

        return new Response();
    }

    #[Route('/game/{id}/move/{cell}', name: 'app_game_move')]
    public function move(Game $game, int $cell): Response
    {
        #TODO update game board
        #TODO check if cell is correct
        #TODO check the winner
        return new Response();
    }

    #[Route('/game/{id}/leave', name: 'app_game_leave')]
    public function leave(): Response
    {
        #TODO finish the game
        return new Response();
    }
}
