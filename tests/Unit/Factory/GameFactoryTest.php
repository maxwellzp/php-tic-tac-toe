<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\Game;
use App\Entity\User;
use App\Factory\GameFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GameFactory::class)]
class GameFactoryTest extends TestCase
{
    public function testCreateCanCreateGame()
    {
        $factory = new GameFactory();
        $game = $factory->create(new User());
        $this->assertInstanceOf(Game::class, $game);
    }
}
