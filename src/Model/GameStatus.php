<?php

declare(strict_types=1);

namespace App\Model;

enum GameStatus: string
{
    case WAITING = 'waiting';
    case FINISHED = 'finished';
    case PLAYING = 'playing';
}
