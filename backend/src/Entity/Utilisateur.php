<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\ChangementMotDePasseInput;
use App\Dto\UtilisateurInput;
use App\Repository\UtilisateurRepository;
use App\State\ChangementMotDePasseProcessor;
use App\State\UtilisateurDeleteProcessor;
use App\State\UtilisateurMeProvider;
use App\State\UtilisateurWriteProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse email est déjà utilisée.')]
#[ApiResource(operations: [
    new GetCollection(uriTemplate: '/utilisateurs', security: "is_granted('ROLE_ADMIN')", normalizationContext: ['groups' => ['utilisateur:list']]),
    new Get(uriTemplate: '/utilisateurs/{id}', security: "is_granted('ROLE_ADMIN')", normalizationContext: ['groups' => ['utilisateur:read']]),
    new Post(uriTemplate: '/utilisateurs', security: "is_granted('ROLE_ADMIN')", input: UtilisateurInput::class, normalizationContext: ['groups' => ['utilisateur:read']], processor: UtilisateurWriteProcessor::class),
    new Patch(uriTemplate: '/utilisateurs/{id}', security: "is_granted('ROLE_ADMIN')", read: false, input: UtilisateurInput::class, normalizationContext: ['groups' => ['utilisateur:read']], processor: UtilisateurWriteProcessor::class),
    new Delete(uriTemplate: '/utilisateurs/{id}', security: "is_granted('ROLE_ADMIN')", processor: UtilisateurDeleteProcessor::class),
    new Get(uriTemplate: '/me', security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['utilisateur:read']], provider: UtilisateurMeProvider::class),
    new Patch(uriTemplate: '/me/mot-de-passe', security: "is_granted('ROLE_USER')", read: false, input: ChangementMotDePasseInput::class, normalizationContext: ['groups' => ['utilisateur:read']], processor: ChangementMotDePasseProcessor::class),
])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['utilisateur:list', 'utilisateur:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    #[Groups(['utilisateur:list', 'utilisateur:read'])]
    private string $email = '';

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['utilisateur:list', 'utilisateur:read'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private string $password = '';

    #[ORM\Column]
    #[Groups(['utilisateur:list', 'utilisateur:read'])]
    private bool $actif = true;

    #[ORM\Column]
    #[Groups(['utilisateur:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Groups(['utilisateur:read'])]
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<int, ChirurgiePlanifiee> */
    #[ORM\OneToMany(targetEntity: ChirurgiePlanifiee::class, mappedBy: 'validePar')]
    private Collection $chirurgiesValidees;

    /** @var Collection<int, PreparationMateriel> */
    #[ORM\OneToMany(targetEntity: PreparationMateriel::class, mappedBy: 'cochePar')]
    private Collection $preparationsCochees;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
        $this->chirurgiesValidees = new ArrayCollection();
        $this->preparationsCochees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = mb_strtolower(trim($email));

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        if ('' === $this->email) {
            throw new \LogicException('An authenticated utilisateur must have an email address.');
        }

        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    public function isActif(): bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): static
    {
        $this->actif = $actif;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /** @return Collection<int, ChirurgiePlanifiee> */
    public function getChirurgiesValidees(): Collection
    {
        return $this->chirurgiesValidees;
    }

    /** @return Collection<int, PreparationMateriel> */
    public function getPreparationsCochees(): Collection
    {
        return $this->preparationsCochees;
    }
}
