<?php

namespace App\Entity;

use App\Repository\OpeningTimeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OpeningTimeRepository::class)]
class OpeningTime
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $text = null;

    #[ORM\Column(length: 20)]
    private ?string $start = null;

    #[ORM\Column(length: 20)]
    private ?string $end = null;

    #[ORM\ManyToOne(inversedBy: 'openingTimeObjects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?StationDetail $stationDetail = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getStart(): ?string
    {
        return $this->start;
    }

    public function setStart(string $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?string
    {
        return $this->end;
    }

    public function setEnd(string $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function getStationDetail(): ?StationDetail
    {
        return $this->stationDetail;
    }

    public function setStationDetail(?StationDetail $stationDetail): static
    {
        $this->stationDetail = $stationDetail;

        return $this;
    }
}
