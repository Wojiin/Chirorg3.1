<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use App\Dto\PreparationMaterielInput;
use App\Repository\PreparationMaterielRepository;
use App\State\PreparationMaterielCocherProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PreparationMaterielRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_preparation_chirurgie_materiel', columns: ['chirurgie_planifiee_id', 'materiel_id'])]
#[ApiResource(operations: [
    new GetCollection(uriTemplate: '/preparations-materiel', security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['preparation_materiel:list']]),
    new GetCollection(uriTemplate: '/chirurgies-planifiees/{id}/preparations-materiel', uriVariables: ['id' => new Link(fromClass: ChirurgiePlanifiee::class, toProperty: 'chirurgiePlanifiee')], security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['preparation_materiel:list']]),
    new Get(uriTemplate: '/preparations-materiel/{id}', security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['preparation_materiel:read']]),
    new Patch(uriTemplate: '/preparations-materiel/{id}', security: "is_granted('ROLE_USER')", read: false, input: PreparationMaterielInput::class, normalizationContext: ['groups' => ['preparation_materiel:read']], processor: PreparationMaterielCocherProcessor::class),
    new Patch(uriTemplate: '/preparations-materiel/{id}/cocher', security: "is_granted('ROLE_USER')", read: false, input: PreparationMaterielInput::class, normalizationContext: ['groups' => ['preparation_materiel:read']], processor: PreparationMaterielCocherProcessor::class),
    new Delete(uriTemplate: '/preparations-materiel/{id}', security: "is_granted('ROLE_ADMIN')"),
])]
class PreparationMateriel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['preparation_materiel:list', 'preparation_materiel:read', 'chirurgie_planifiee:read', 'programme:read'])]
    private ?int $id = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['preparation_materiel:list', 'preparation_materiel:read', 'chirurgie_planifiee:read', 'programme:read'])]
    private bool $coche = false;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['preparation_materiel:list', 'preparation_materiel:read', 'chirurgie_planifiee:read', 'programme:read'])]
    private bool $absent = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['preparation_materiel:read', 'chirurgie_planifiee:read'])]
    private ?\DateTimeImmutable $cocheLe = null;

    #[ORM\ManyToOne(inversedBy: 'preparationsMateriel')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?ChirurgiePlanifiee $chirurgiePlanifiee = null;

    #[ORM\ManyToOne(inversedBy: 'preparationsMateriel')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['preparation_materiel:list', 'preparation_materiel:read', 'chirurgie_planifiee:read', 'programme:read'])]
    private ?Materiel $materiel = null;

    #[ORM\ManyToOne(inversedBy: 'preparationsCochees')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $cochePar = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isCoche(): bool
    {
        return $this->coche;
    }

    public function setCoche(bool $value): static
    {
        $this->coche = $value;

        return $this;
    }

    public function isAbsent(): bool
    {
        return $this->absent;
    }

    public function setAbsent(bool $value): static
    {
        $this->absent = $value;

        return $this;
    }

    public function getCocheLe(): ?\DateTimeImmutable
    {
        return $this->cocheLe;
    }

    public function setCocheLe(?\DateTimeImmutable $value): static
    {
        $this->cocheLe = $value;

        return $this;
    }

    public function getChirurgiePlanifiee(): ?ChirurgiePlanifiee
    {
        return $this->chirurgiePlanifiee;
    }

    public function setChirurgiePlanifiee(?ChirurgiePlanifiee $value): static
    {
        $this->chirurgiePlanifiee = $value;

        return $this;
    }

    public function getMateriel(): ?Materiel
    {
        return $this->materiel;
    }

    public function setMateriel(?Materiel $value): static
    {
        $this->materiel = $value;

        return $this;
    }

    public function getCochePar(): ?Utilisateur
    {
        return $this->cochePar;
    }

    public function setCochePar(?Utilisateur $value): static
    {
        $this->cochePar = $value;

        return $this;
    }
}
