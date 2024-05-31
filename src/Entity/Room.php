<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $roomType = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $maximumCapacity = null;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'room')]
    #[Ignore]
    private Collection $reservations;

    #[ORM\ManyToOne(inversedBy: 'rooms')]
    #[Ignore]
    private ?Accommodation $accommodation = null;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoomType(): ?string
    {
        return $this->roomType;
    }

    public function setRoomType(?string $roomType): static
    {
        $this->roomType = $roomType;

        return $this;
    }

    public function getMaximumCapacity(): ?int
    {
        return $this->maximumCapacity;
    }

    public function setMaximumCapacity(?int $maximumCapacity): static
    {
        $this->maximumCapacity = $maximumCapacity;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setRoom($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getRoom() === $this) {
                $reservation->setRoom(null);
            }
        }

        return $this;
    }

    public function getAccommodation(): ?Accommodation
    {
        return $this->accommodation;
    }

    public function setAccommodation(?Accommodation $accommodation): static
    {
        $this->accommodation = $accommodation;

        return $this;
    }
}
