<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Patch;
use App\Dto\PreparationMaterielInput;
use App\State\PreparationMaterielCocherProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uniq_preparation_chirurgie_materiel', columns: ['chirurgie_planifiee_id', 'materiel_id'])]
#[ApiResource(operations: [
    new Patch(uriTemplate: '/preparations-materiel/{id}/cocher', security: "is_granted('ROLE_USER')", read: false, input: PreparationMaterielInput::class, normalizationContext: ['groups' => ['preparation_materiel:read']], processor: PreparationMaterielCocherProcessor::class),
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

    // Retourne l'identifiant de la préparation du matériel. Dépendances utilisées : aucune dépendance de service directe.
    public function getId(): ?int
    {
        return $this->id;
    }

    // Indique si le matériel a été contrôlé pendant la préparation. Dépendances utilisées : aucune dépendance de service directe.
    public function isCoche(): bool
    {
        return $this->coche;
    }

    // Met à jour l'état contrôlé du matériel préparé. Dépendances utilisées : aucune dépendance de service directe.
    public function setCoche(bool $value): static
    {
        $this->coche = $value;

        return $this;
    }

    // Indique si le matériel est signalé absent pendant la préparation. Dépendances utilisées : aucune dépendance de service directe.
    public function isAbsent(): bool
    {
        return $this->absent;
    }

    // Met à jour l'état absent du matériel préparé. Dépendances utilisées : aucune dépendance de service directe.
    public function setAbsent(bool $value): static
    {
        $this->absent = $value;

        return $this;
    }

    // Retourne la date de contrôle du matériel de la préparation du matériel. Dépendances utilisées : aucune dépendance de service directe.
    public function getCocheLe(): ?\DateTimeImmutable
    {
        return $this->cocheLe;
    }

    // Met à jour la date de contrôle du matériel de la préparation du matériel. Dépendances utilisées : aucune dépendance de service directe.
    public function setCocheLe(?\DateTimeImmutable $value): static
    {
        $this->cocheLe = $value;

        return $this;
    }

    // Retourne la chirurgie planifiée de la préparation du matériel. Dépendances utilisées : ChirurgiePlanifiee.
    public function getChirurgiePlanifiee(): ?ChirurgiePlanifiee
    {
        return $this->chirurgiePlanifiee;
    }

    // Met à jour la chirurgie planifiée de la préparation du matériel. Dépendances utilisées : ChirurgiePlanifiee.
    public function setChirurgiePlanifiee(?ChirurgiePlanifiee $value): static
    {
        $this->chirurgiePlanifiee = $value;

        return $this;
    }

    // Retourne le matériel chirurgical de la préparation du matériel. Dépendances utilisées : Materiel.
    public function getMateriel(): ?Materiel
    {
        return $this->materiel;
    }

    // Met à jour le matériel chirurgical de la préparation du matériel. Dépendances utilisées : Materiel.
    public function setMateriel(?Materiel $value): static
    {
        $this->materiel = $value;

        return $this;
    }

    // Retourne l'utilisateur ayant contrôlé le matériel de la préparation du matériel. Dépendances utilisées : Utilisateur.
    public function getCochePar(): ?Utilisateur
    {
        return $this->cochePar;
    }

    // Met à jour l'utilisateur ayant contrôlé le matériel de la préparation du matériel. Dépendances utilisées : Utilisateur.
    public function setCochePar(?Utilisateur $value): static
    {
        $this->cochePar = $value;

        return $this;
    }
}
