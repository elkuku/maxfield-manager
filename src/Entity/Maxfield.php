<?php

namespace App\Entity;

use App\Repository\MaxfieldRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaxfieldRepository::class)]
class Maxfield
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id = 0;

    #[ORM\Column(type: 'string', length: 150)]
    private ?string $name;

    #[ORM\Column(type: 'text')]
    private ?string $gpx;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'maxfields')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getGpx(): ?string
    {
        return $this->gpx;
    }

    public function setGpx(string $gpx): self
    {
        $this->gpx = $gpx;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
