<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Game;
use App\Service\MercureService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Uid\Uuid;

#[CoversClass(MercureService::class)]
class MercureServiceTest extends TestCase
{
    public function testPublishGameUpdate(): void
    {
        $game = $this->createMock(Game::class);

        $uuid = Uuid::v7();
        $game->method('getId')->willReturn($uuid);
        $expectedData = ['status' => 'in_progress'];

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) use ($expectedData, $uuid) {
                return
                    $update->getTopics() === ['/game/' . $uuid] &&
                    $update->getData() === json_encode($expectedData);
            }));

        $service = new MercureService($hub);
        $service->publishGameUpdate($game, $expectedData);
    }

    public function testPublishNewGameAvailable(): void
    {
        $game = $this->createMock(Game::class);

        $uuid = Uuid::v7();
        $game->method('getId')->willReturn($uuid);
        $expectedData = ['type' => 'new_game', 'id' => $uuid];

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) use ($expectedData, $uuid) {
                return
                    $update->getTopics() === ['/games'] &&
                    $update->getData() === json_encode($expectedData);
            }));

        $service = new MercureService($hub);
        $service->publishNewGameAvailable($game);
    }

    public function testPublishGameStarted()
    {
        $game = $this->createMock(Game::class);

        $uuid = Uuid::v7();
        $game->method('getId')->willReturn($uuid);
        $expectedData = ['type' => 'game_start', 'id' => $uuid];

        $hub = $this->createMock(HubInterface::class);
        $hub->expects($this->once())
            ->method('publish')
            ->with($this->callback(function (Update $update) use ($expectedData, $uuid) {
                return
                    $update->getTopics() === ['/game/' . $uuid] &&
                    $update->getData() === json_encode($expectedData);
            }));

        $service = new MercureService($hub);
        $service->publishGameStarted($game);
    }
}
