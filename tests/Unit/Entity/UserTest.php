<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
class UserTest extends TestCase
{
    public function testCanGetAndSetData()
    {
        $user = new User();
        $user->setEmail('test@test.com');
        $user->setPassword('password');
        $user->setRoles(['ROLE_USER']);

        $this->assertSame($user->getEmail(), 'test@test.com');
        $this->assertSame($user->getPassword(), 'password');
        $this->assertSame($user->getRoles(), ['ROLE_USER']);
    }
}
