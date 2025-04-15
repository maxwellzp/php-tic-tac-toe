<?php

declare(strict_types=1);

namespace App\Model;

enum GameTurn: string
{
    case X_TURN = 'X';
    case O_TURN = 'O';
}
