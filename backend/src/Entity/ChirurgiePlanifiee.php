<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Dto\ChirurgiePreparation;
use App\Dto\ChirurgieVueFinale;
use App\Error\ErrorMessage;
use App\Repository\ChirurgiePlanifieeRepository;
use App\State\ChirurgiePreparationProvider;
use App\State\ChirurgieValidationProcessor;
use App\State\ReferenceDeleteProcessor;
use App\State\VueFinaleProvider;
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
#[ORM\UniqueConstraint(name: 'uniq_chirurgie_programme_ordre', columns: ['date_programmee', 'salle', 'chirurgien_id', 'ordre'])]
#[ApiResource(operations: [
    new Get(uriTemplate: '/chirurgies-planifiees/{id}/preparation', security: "is_granted('ROLE_USER')", output: ChirurgiePreparation::class, provider: ChirurgiePreparationProvider::class),
    new Get(uriTemplate: '/chirurgies-planifiees/{id}/vue-finale', security: "is_granted('ROLE_USER')", output: ChirurgieVueFinale::class, provider: VueFinaleProvider::class),
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
    #[Assert\NotNull(message: ErrorMessage::REQUIRED_FIELD)]
    #[Groups(['chirurgie_planifiee:list', 'chirurgie_planifiee:read', 'chirurgie_planifiee:write', 'programme:read'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private ?\DateTimeImmutable $dateProgrammee = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
    #[Assert\Length(max: 50, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
    #[Groups(['chirurgie_planifiee:list', 'chirurgie_planifiee:read', 'chirurgie_planifiee:write', 'programme:read'])]
    private ?string $salle = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: ErrorMessage::POSITIVE_NUMBER_REQUIRED)]
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
    #[Assert\NotNull(message: ErrorMessage::REQUIRED_FIELD)]
    #[Groups(['chirurgie_planifiee:list', 'chirurgie_planifiee:read', 'chirurgie_planifiee:write', 'programme:read'])]
    private ?Chirurgien $chirurgien = null;

    #[ORM\ManyToOne(inversedBy: 'chirurgiesPlanifiees')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: ErrorMessage::REQUIRED_FIELD)]
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

    // Initialise l'entité ChirurgiePlanifiee représentant la chirurgie planifiée. Dépendances utilisées : ArrayCollection, Collection.
    public function __construct()
    {
        $this->preparationsMateriel = new ArrayCollection();
        $now = new \DateTimeImmutable();
        $this->creeLe = $now;
        $this->modifieLe = $now;
    }

    // Retourne l'identifiant de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function getId(): ?int
    {
        return $this->id;
    }

    // Retourne la date programmée de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function getDateProgrammee(): ?\DateTimeImmutable
    {
        return $this->dateProgrammee;
    }

    // Met à jour la date programmée de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function setDateProgrammee(\DateTimeImmutable $value): static
    {
        $this->dateProgrammee = $value;

        return $this;
    }

    // Retourne la salle opératoire de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function getSalle(): ?string
    {
        return $this->salle;
    }

    // Met à jour la salle opératoire de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function setSalle(string $value): static
    {
        $this->salle = trim($value);

        return $this;
    }

    // Retourne l'ordre de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    // Met à jour l'ordre de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function setOrdre(?int $value): static
    {
        $this->ordre = $value;

        return $this;
    }

    // Indique si la chirurgie planifiée a été validée. Dépendances utilisées : aucune dépendance de service directe.
    public function isValide(): bool
    {
        return $this->valide;
    }

    // Met à jour le statut de validation de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function setValide(bool $value): static
    {
        $this->valide = $value;

        return $this;
    }

    // Retourne la date de validation de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function getValideLe(): ?\DateTimeImmutable
    {
        return $this->valideLe;
    }

    // Met à jour la date de validation de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function setValideLe(?\DateTimeImmutable $value): static
    {
        $this->valideLe = $value;

        return $this;
    }

    // Retourne le chirurgien de la chirurgie planifiée. Dépendances utilisées : Chirurgien.
    public function getChirurgien(): ?Chirurgien
    {
        return $this->chirurgien;
    }

    // Met à jour le chirurgien de la chirurgie planifiée. Dépendances utilisées : Chirurgien.
    public function setChirurgien(?Chirurgien $value): static
    {
        $this->chirurgien = $value;

        return $this;
    }

    // Retourne le modèle de chirurgie de la chirurgie planifiée. Dépendances utilisées : ChirurgieModele.
    public function getChirurgieModele(): ?ChirurgieModele
    {
        return $this->chirurgieModele;
    }

    // Met à jour le modèle de chirurgie de la chirurgie planifiée. Dépendances utilisées : ChirurgieModele.
    public function setChirurgieModele(?ChirurgieModele $value): static
    {
        $this->chirurgieModele = $value;

        return $this;
    }

    // Retourne l'utilisateur ayant effectué la validation de la chirurgie planifiée. Dépendances utilisées : Utilisateur.
    public function getValidePar(): ?Utilisateur
    {
        return $this->validePar;
    }

    // Met à jour l'utilisateur ayant effectué la validation de la chirurgie planifiée. Dépendances utilisées : Utilisateur.
    public function setValidePar(?Utilisateur $value): static
    {
        $this->validePar = $value;

        return $this;
    }

    // Retourne la date de création de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function getCreeLe(): \DateTimeImmutable
    {
        return $this->creeLe;
    }

    // Met à jour la date de création de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function setCreeLe(\DateTimeImmutable $value): static
    {
        $this->creeLe = $value;

        return $this;
    }

    // Retourne la date de modification de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function getModifieLe(): \DateTimeImmutable
    {
        return $this->modifieLe;
    }

    // Met à jour la date de modification de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function setModifieLe(\DateTimeImmutable $value): static
    {
        $this->modifieLe = $value;

        return $this;
    }

    // Retourne l'auteur de la création de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function getCreePar(): ?string
    {
        return $this->creePar;
    }

    // Met à jour l'auteur de la création de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function setCreePar(?string $value): static
    {
        $this->creePar = $value;

        return $this;
    }

    // Retourne l'auteur de la dernière modification de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function getModifiePar(): ?string
    {
        return $this->modifiePar;
    }

    // Met à jour l'auteur de la dernière modification de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function setModifiePar(?string $value): static
    {
        $this->modifiePar = $value;

        return $this;
    }

    // Retourne les préparations de matériel de la chirurgie planifiée. Dépendances utilisées : Collection.
    /** @return Collection<int, PreparationMateriel> */
    public function getPreparationsMateriel(): Collection
    {
        return $this->preparationsMateriel;
    }

    // Ajoute la préparation du matériel à la chirurgie planifiée. Dépendances utilisées : Collection, PreparationMateriel.
    public function addPreparationMateriel(PreparationMateriel $value): static
    {
        if (!$this->preparationsMateriel->contains($value)) {
            $this->preparationsMateriel->add($value);
            $value->setChirurgiePlanifiee($this);
        }

        return $this;
    }

    // Retire la préparation du matériel de la chirurgie planifiée. Dépendances utilisées : Collection, PreparationMateriel.
    public function removePreparationMateriel(PreparationMateriel $value): static
    {
        if ($this->preparationsMateriel->removeElement($value) && $value->getChirurgiePlanifiee() === $this) {
            $value->setChirurgiePlanifiee(null);
        }

        return $this;
    }
}
