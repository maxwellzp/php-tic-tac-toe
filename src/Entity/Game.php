<?php

namespace App\Entity;

use App\Model\GameStatus;
use App\Model\GameTurn;
use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column]
    private array $board = ["", "", "", "", "", "", "", "", ""];
    /*
        0 | 1 | 2
        ---------
        3 | 4 | 5
        ---------
        6 | 7 | 8
     */

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(enumType: GameStatus::class)]
    private ?GameStatus $status = null;

    #[ORM\Column(enumType: GameTurn::class)]
    private ?GameTurn $currentTurn = null;

    #[ORM\Column(nullable: true, enumType: GameTurn::class)]
    private ?GameTurn $winner = null;

    #[ORM\ManyToOne(inversedBy: 'games')]
    private ?User $userX = null;

    #[ORM\ManyToOne(inversedBy: 'games')]
    private ?User $userO = null;

    public function __construct()
    {
        $this->currentTurn = GameTurn::X_TURN;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getBoard(): array
    {
        return $this->board;
    }

    public function setBoard(array $board): static
    {
        $this->board = $board;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStatus(): ?GameStatus
    {
        return $this->status;
    }

    public function setStatus(GameStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCurrentTurn(): ?GameTurn
    {
        return $this->currentTurn;
    }

    public function setCurrentTurn(GameTurn $currentTurn): static
    {
        $this->currentTurn = $currentTurn;

        return $this;
    }

    public function getWinner(): ?GameTurn
    {
        return $this->winner;
    }

    public function setWinner(?GameTurn $winner): static
    {
        $this->winner = $winner;

        return $this;
    }

    public function getUserX(): ?User
    {
        return $this->userX;
    }

    public function setUserX(?User $userX): static
    {
        $this->userX = $userX;

        return $this;
    }

    public function getUserO(): ?User
    {
        return $this->userO;
    }

    public function setUserO(?User $userO): static
    {
        $this->userO = $userO;

        return $this;
    }

    public function isCurrentPlayer(?User $user): bool
    {
        if ($this->currentTurn === GameTurn::X_TURN) {
            return $this->userX === $user;
        }
        return $this->userO === $user;
    }
}
