<?php

namespace App\Entity;

use App\Repository\AccommodationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: AccommodationRepository::class)]
class Accommodation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $postalCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeAccommodation = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $numberRooms = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $services = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $img = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $checkIn = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $checkOut = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?bool $hidden = null;

    #[ORM\OneToMany(targetEntity: Room::class, mappedBy: 'accommodation', cascade: ['persist'])]
    private Collection $rooms;

    #[ORM\ManyToOne(inversedBy: 'accommodations')]
    private ?City $city = null;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'accommodation')]
    private Collection $reviews;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $price = null;

    public function __construct()
    {
        $this->rooms = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(?int $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getTypeAccommodation(): ?string
    {
        return $this->typeAccommodation;
    }

    public function setTypeAccommodation(?string $typeAccommodation): static
    {
        $this->typeAccommodation = $typeAccommodation;

        return $this;
    }

    public function getNumberRooms(): ?int
    {
        return $this->numberRooms;
    }

    public function setNumberRooms(int $numberRooms): static
    {
        $this->numberRooms = $numberRooms;

        return $this;
    }

    public function getServices(): ?array
    {
        return $this->services;
    }

    public function setServices(?array $services): static
    {
        $this->services = $services;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getImg(): ?array
    {
        return $this->img;
    }

    public function setImg(?array $img): static
    {
        $this->img = $img;

        return $this;
    }

    public function getCheckIn(): ?\DateTimeInterface
    {
        return $this->checkIn;
    }

    public function setCheckIn(?\DateTimeInterface $checkIn): static
    {
        $this->checkIn = $checkIn;

        return $this;
    }

    public function getCheckOut(): ?\DateTimeInterface
    {
        return $this->checkOut;
    }

    public function setCheckOut(?\DateTimeInterface $checkOut): static
    {
        $this->checkOut = $checkOut;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isHidden(): ?bool
    {
        return $this->hidden;
    }

    public function setHidden(?bool $hidden): static
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): static
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->setAccommodation($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): static
    {
        if ($this->rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getAccommodation() === $this) {
                $room->setAccommodation(null);
            }
        }

        return $this;
    }

    public function createRooms(int $numberOfRooms, int $maximumCapacity): void
    {
        for ($i = 0; $i < $numberOfRooms; $i++) {
            $room = new Room();
            $room->setMaximumCapacity($maximumCapacity);
            // Asignar cualquier información adicional a la habitación si es necesario
            // $room->setTipo($this->getTipo()); // Ejemplo de asignación de tipo de habitación basado en la configuración de alojamiento
            $this->addRoom($room);
        }
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setAccommodation($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getAccommodation() === $this) {
                $review->setAccommodation(null);
            }
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }
    public function getTypesAccommodations() : array 
    {
        return ['Hotel', 'Casa', 'Apartamento', 'Escapadas Rurales', 'Escapadas Urbanas', 'Escapada Familia', 'Escapada con Amigos'];
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): static
    {
        $this->price = $price;

        return $this;
    }
}
