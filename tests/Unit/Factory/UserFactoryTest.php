<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Entity\User;
use App\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserFactory::class)]
class UserFactoryTest extends TestCase
{
    public function testCreateCanCreateUser(): void
    {
        $factory = new UserFactory();
        $user = $factory->create('test@test.test', 'password');
        $this->assertInstanceOf(User::class, $user);
    }
}
