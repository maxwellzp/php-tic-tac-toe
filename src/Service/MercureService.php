<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureService
{
    public function __construct(private readonly HubInterface $hub)
    {
    }

    public function publishGameUpdate(Game $game, array $data): void
    {
        $update = new Update(
            sprintf('/game/%s', $game->getId()),
            json_encode($data)
        );
        $this->hub->publish($update);
    }

    public function publishNewGameAvailable(Game $game): void
    {
        $update = new Update(
            '/games',
            json_encode(['type' => 'new_game', 'id' => $game->getId()])
        );
        $this->hub->publish($update);
    }

    public function publishGameStarted(Game $game): void
    {
        $update = new Update(
            sprintf('/game/%s', $game->getId()),
            json_encode(['type' => 'game_start', 'id' => $game->getId()])
        );
        $this->hub->publish($update);
    }
}
