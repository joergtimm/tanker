<?php

namespace App\Entity;

use App\Repository\MoveRepository;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

#[Entity(repositoryClass: MoveRepository::class)]
#[Table(name: 'move')]
class Move
{
    #[Id]
    #[GeneratedValue]
    #[Column]
    private ?int $id = null;

    #[ManyToOne(targetEntity: Game::class, inversedBy: 'moves')]
    #[JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: true)] // null for local second player if we don't have a user
    private ?User $player = null;

    #[Column]
    private ?int $playerNumber = null; // 1 or 2

    #[Column]
    private ?int $columnNumber = null;

    #[Column]
    private ?int $boardIndex = null;

    #[Column(type: 'json', nullable: true)]
    private ?array $boardState = null;

    #[Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;
        return $this;
    }

    public function getPlayer(): ?User
    {
        return $this->player;
    }

    public function setPlayer(?User $player): static
    {
        $this->player = $player;
        return $this;
    }

    public function getPlayerNumber(): ?int
    {
        return $this->playerNumber;
    }

    public function setPlayerNumber(int $playerNumber): static
    {
        $this->playerNumber = $playerNumber;
        return $this;
    }

    public function getColumnNumber(): ?int
    {
        return $this->columnNumber;
    }

    public function setColumnNumber(int $columnNumber): static
    {
        $this->columnNumber = $columnNumber;
        return $this;
    }

    public function getBoardIndex(): ?int
    {
        return $this->boardIndex;
    }

    public function setBoardIndex(int $boardIndex): static
    {
        $this->boardIndex = $boardIndex;
        return $this;
    }

    public function getBoardState(): ?array
    {
        return $this->boardState;
    }

    public function setBoardState(?array $boardState): static
    {
        $this->boardState = $boardState;
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
}
