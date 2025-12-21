<?php

namespace App\Entity;

use App\Repository\StationDetailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StationDetailRepository::class)]
class StationDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'stationDetail', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Station $station = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $openingTimes = null;

    /**
     * @var Collection<int, OpeningTime>
     */
    #[ORM\OneToMany(targetEntity: OpeningTime::class, mappedBy: 'stationDetail', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $openingTimeObjects;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $overrides = null;

    #[ORM\Column(nullable: true)]
    private ?bool $wholeDay = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $state = null;

    public function __construct()
    {
        $this->openingTimeObjects = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStation(): ?Station
    {
        return $this->station;
    }

    public function setStation(Station $station): static
    {
        $this->station = $station;

        return $this;
    }

    public function getOpeningTimes(): ?array
    {
        return $this->openingTimes;
    }

    public function setOpeningTimes(?array $openingTimes): static
    {
        $this->openingTimes = $openingTimes;

        return $this;
    }

    public function getOverrides(): ?array
    {
        return $this->overrides;
    }

    public function setOverrides(?array $overrides): static
    {
        $this->overrides = $overrides;

        return $this;
    }

    public function isWholeDay(): ?bool
    {
        return $this->wholeDay;
    }

    public function setWholeDay(?bool $wholeDay): static
    {
        $this->wholeDay = $wholeDay;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return Collection<int, OpeningTime>
     */
    public function getOpeningTimeObjects(): Collection
    {
        return $this->openingTimeObjects;
    }

    public function addOpeningTimeObject(OpeningTime $openingTimeObject): static
    {
        if (!$this->openingTimeObjects->contains($openingTimeObject)) {
            $this->openingTimeObjects->add($openingTimeObject);
            $openingTimeObject->setStationDetail($this);
        }

        return $this;
    }

    public function removeOpeningTimeObject(OpeningTime $openingTimeObject): static
    {
        if ($this->openingTimeObjects->removeElement($openingTimeObject)) {
            // set the owning side to null (unless already changed)
            if ($openingTimeObject->getStationDetail() === $this) {
                $openingTimeObject->setStationDetail(null);
            }
        }

        return $this;
    }
}
