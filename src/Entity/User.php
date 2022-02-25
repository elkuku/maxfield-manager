<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Entity(repositoryClass: UserRepository::class)]
#[Table(name: 'system_user')]
#[UniqueEntity(fields: 'identifier', message: 'This identifier is already in use')]
class User implements UserInterface
{
    public final const ROLES
        = [
            'user'  => 'ROLE_USER',
            'admin' => 'ROLE_ADMIN',
        ];

    #[Id, GeneratedValue(strategy: 'AUTO')]
    #[Column(type: Types::INTEGER)]
    private ?int $id = 0;

    #[Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank]
    private string $identifier = '';

    /**
     * @var array<string>
     */
    #[Column(type: Types::JSON)]
    private array $roles = [];

    #[Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $googleId = '';

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $gitHubId = '';

    /**
     * @var Collection<int, Maxfield>
     */
    #[OneToMany(mappedBy: 'owner', targetEntity: Maxfield::class)]
    private Collection $maxfields;

    public function __construct()
    {
        $this->maxfields = new ArrayCollection();
    }

    /**
     * @return array{ id: integer|null, identifier: string|null}
     */
    public function __serialize(): array
    {
        return [
            'id'         => $this->id,
            'identifier' => $this->identifier,
        ];
    }

    /**
     * @param array{ id: int, identifier: string} $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->identifier = $data['identifier'];
    }

    public function __toString()
    {
        return $this->identifier;
    }

    public function eraseCredentials(): void
    {
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }

    public function getGitHubId(): ?string
    {
        return $this->gitHubId;
    }

    public function setGitHubId(?string $gitHubId): self
    {
        $this->gitHubId = $gitHubId;

        return $this;
    }

    /**
     * @return Collection<int, Maxfield>
     */
    public function getMaxfields(): Collection
    {
        return $this->maxfields;
    }

    public function addMaxfield(Maxfield $maxfield): self
    {
        if (!$this->maxfields->contains($maxfield)) {
            $this->maxfields[] = $maxfield;
            $maxfield->setOwner($this);
        }

        return $this;
    }

    public function removeMaxfield(Maxfield $maxfield): self
    {
        if ($this->maxfields->removeElement($maxfield)) {
            // set the owning side to null (unless already changed)
            if ($maxfield->getOwner() === $this) {
                $maxfield->setOwner(null);
            }
        }

        return $this;
    }
}
