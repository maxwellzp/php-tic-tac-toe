<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game_index')]
    public function index(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->findAll();
        return $this->render('game/index.html.twig', [
            'controller_name' => 'GameController',
        ]);
    }

    #[Route('/game/new', name: 'app_game_new')]
    public function new(): Response
    {
        return new Response();
    }

    #[Route('/game/{id}/join', name: 'app_game_join')]
    public function join(): Response
    {
        return new Response();
    }
}
