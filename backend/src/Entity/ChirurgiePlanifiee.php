<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\ChirurgiePlanifieeRepository;
use App\State\ChirurgiePlanifieeWriteProcessor;
use App\State\ChirurgieValidationProcessor;
use App\State\ReferenceDeleteProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChirurgiePlanifieeRepository::class)]
#[ORM\Index(name: 'idx_chirurgie_date', columns: ['date_programmee'])]
#[ORM\Index(name: 'idx_chirurgie_programme', columns: ['date_programmee', 'salle', 'chirurgien_id'])]
#[ApiResource(operations: [
    new GetCollection(uriTemplate: '/chirurgies-planifiees', security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['chirurgie_planifiee:list']]),
    new Get(uriTemplate: '/chirurgies-planifiees/{id}', security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['chirurgie_planifiee:read']]),
    new Post(uriTemplate: '/chirurgies-planifiees', security: "is_granted('ROLE_USER')", denormalizationContext: ['groups' => ['chirurgie_planifiee:write']], normalizationContext: ['groups' => ['chirurgie_planifiee:read']], processor: ChirurgiePlanifieeWriteProcessor::class),
    new Patch(uriTemplate: '/chirurgies-planifiees/{id}', security: "is_granted('ROLE_USER')", denormalizationContext: ['groups' => ['chirurgie_planifiee:write']], normalizationContext: ['groups' => ['chirurgie_planifiee:read']], processor: ChirurgiePlanifieeWriteProcessor::class),
    new Post(uriTemplate: '/chirurgies-planifiees/{id}/validation', security: "is_granted('ROLE_USER')", input: false, normalizationContext: ['groups' => ['chirurgie_planifiee:read']], processor: ChirurgieValidationProcessor::class),
    new Delete(uriTemplate: '/chirurgies-planifiees/{id}', security: "is_granted('ROLE_USER')", processor: ReferenceDeleteProcessor::class),
])]
class ChirurgiePlanifiee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['chirurgie_planifiee:list', 'chirurgie_planifiee:read', 'programme:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    #[Groups(['chirurgie_planifiee:list', 'chirurgie_planifiee:read', 'chirurgie_planifiee:write', 'programme:read'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private ?\DateTimeImmutable $dateProgrammee = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[Groups(['chirurgie_planifiee:list', 'chirurgie_planifiee:read', 'chirurgie_planifiee:write', 'programme:read'])]
    private ?string $salle = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive]
    #[Groups(['chirurgie_planifiee:list', 'chirurgie_planifiee:read', 'programme:read'])]
    private ?int $ordre = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['chirurgie_planifiee:list', 'chirurgie_planifiee:read', 'programme:read'])]
    private bool $valide = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['chirurgie_planifiee:read'])]
    private ?\DateTimeImmutable $valideLe = null;

    #[ORM\ManyToOne(inversedBy: 'chirurgiesPlanifiees')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['chirurgie_planifiee:list', 'chirurgie_planifiee:read', 'chirurgie_planifiee:write', 'programme:read'])]
    private ?Chirurgien $chirurgien = null;

    #[ORM\ManyToOne(inversedBy: 'chirurgiesPlanifiees')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['chirurgie_planifiee:list', 'chirurgie_planifiee:read', 'chirurgie_planifiee:write', 'programme:read'])]
    private ?ChirurgieModele $chirurgieModele = null;

    #[ORM\ManyToOne(inversedBy: 'chirurgiesValidees')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Utilisateur $validePar = null;

    #[ORM\Column]
    private \DateTimeImmutable $creeLe;

    #[ORM\Column]
    private \DateTimeImmutable $modifieLe;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $creePar = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $modifiePar = null;

    /** @var Collection<int, PreparationMateriel> */
    #[ORM\OneToMany(targetEntity: PreparationMateriel::class, mappedBy: 'chirurgiePlanifiee', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['chirurgie_planifiee:read'])]
    private Collection $preparationsMateriel;

    public function __construct()
    {
        $this->preparationsMateriel = new ArrayCollection();
        $now = new \DateTimeImmutable();
        $this->creeLe = $now;
        $this->modifieLe = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateProgrammee(): ?\DateTimeImmutable
    {
        return $this->dateProgrammee;
    }

    public function setDateProgrammee(\DateTimeImmutable $value): static
    {
        $this->dateProgrammee = $value;

        return $this;
    }

    public function getSalle(): ?string
    {
        return $this->salle;
    }

    public function setSalle(string $value): static
    {
        $this->salle = trim($value);

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $value): static
    {
        $this->ordre = $value;

        return $this;
    }

    public function isValide(): bool
    {
        return $this->valide;
    }

    public function setValide(bool $value): static
    {
        $this->valide = $value;

        return $this;
    }

    public function getValideLe(): ?\DateTimeImmutable
    {
        return $this->valideLe;
    }

    public function setValideLe(?\DateTimeImmutable $value): static
    {
        $this->valideLe = $value;

        return $this;
    }

    public function getChirurgien(): ?Chirurgien
    {
        return $this->chirurgien;
    }

    public function setChirurgien(?Chirurgien $value): static
    {
        $this->chirurgien = $value;

        return $this;
    }

    public function getChirurgieModele(): ?ChirurgieModele
    {
        return $this->chirurgieModele;
    }

    public function setChirurgieModele(?ChirurgieModele $value): static
    {
        $this->chirurgieModele = $value;

        return $this;
    }

    public function getValidePar(): ?Utilisateur
    {
        return $this->validePar;
    }

    public function setValidePar(?Utilisateur $value): static
    {
        $this->validePar = $value;

        return $this;
    }

    public function getCreeLe(): \DateTimeImmutable
    {
        return $this->creeLe;
    }

    public function setCreeLe(\DateTimeImmutable $value): static
    {
        $this->creeLe = $value;

        return $this;
    }

    public function getModifieLe(): \DateTimeImmutable
    {
        return $this->modifieLe;
    }

    public function setModifieLe(\DateTimeImmutable $value): static
    {
        $this->modifieLe = $value;

        return $this;
    }

    public function getCreePar(): ?string
    {
        return $this->creePar;
    }

    public function setCreePar(?string $value): static
    {
        $this->creePar = $value;

        return $this;
    }

    public function getModifiePar(): ?string
    {
        return $this->modifiePar;
    }

    public function setModifiePar(?string $value): static
    {
        $this->modifiePar = $value;

        return $this;
    }

    /** @return Collection<int, PreparationMateriel> */
    public function getPreparationsMateriel(): Collection
    {
        return $this->preparationsMateriel;
    }

    public function addPreparationMateriel(PreparationMateriel $value): static
    {
        if (!$this->preparationsMateriel->contains($value)) {
            $this->preparationsMateriel->add($value);
            $value->setChirurgiePlanifiee($this);
        }

        return $this;
    }

    public function removePreparationMateriel(PreparationMateriel $value): static
    {
        if ($this->preparationsMateriel->removeElement($value) && $value->getChirurgiePlanifiee() === $this) {
            $value->setChirurgiePlanifiee(null);
        }

        return $this;
    }
}
