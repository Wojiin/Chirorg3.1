<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\FreeTextQueryFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Error\ErrorMessage;
use App\State\ReferenceDeleteProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Index(name: 'idx_materiel_intitule', columns: ['intitule'])]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['materiel:list']],
            parameters: [
                'q' => new QueryParameter(filter: new FreeTextQueryFilter(new OrFilter(new PartialSearchFilter())), properties: ['intitule', 'typeMateriel', 'adresse']),
                'intitule' => new QueryParameter(property: 'intitule', filter: new PartialSearchFilter()),
                'typeMateriel' => new QueryParameter(property: 'typeMateriel', filter: new ExactFilter()),
                'adresse' => new QueryParameter(property: 'adresse', filter: new PartialSearchFilter()),
                'specialite' => new QueryParameter(property: 'specialite', filter: new ExactFilter()),
            ],
        ),
        new Get(security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['materiel:read']]),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['materiel:read']],
            denormalizationContext: ['groups' => ['materiel:write']],
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['materiel:read']],
            denormalizationContext: ['groups' => ['materiel:write']],
        ),
        new Delete(security: "is_granted('ROLE_ADMIN')", processor: ReferenceDeleteProcessor::class),
    ],
    order: ['intitule' => 'ASC'],
)]
class Materiel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['materiel:list', 'materiel:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
    #[Assert\Length(max: 150, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
    #[Groups(['materiel:list', 'materiel:read', 'materiel:write'])]
    private ?string $intitule = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
    #[Groups(['materiel:list', 'materiel:read', 'materiel:write'])]
    private ?string $adresse = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
    #[Groups(['materiel:list', 'materiel:read', 'materiel:write'])]
    private ?string $typeMateriel = null;

    #[ORM\ManyToOne(inversedBy: 'materiels')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: ErrorMessage::REQUIRED_FIELD)]
    #[Groups(['materiel:list', 'materiel:read', 'materiel:write'])]
    private ?Specialite $specialite = null;

    /**
     * @var Collection<int, ListeMateriel>
     */
    #[ORM\ManyToMany(targetEntity: ListeMateriel::class, mappedBy: 'materiels')]
    private Collection $listesMateriel;

    /** @var Collection<int, PreparationMateriel> */
    #[ORM\OneToMany(targetEntity: PreparationMateriel::class, mappedBy: 'materiel')]
    private Collection $preparationsMateriel;

    // Initialise l'entité Materiel représentant le matériel chirurgical. Dépendances utilisées : ArrayCollection, Collection.
    public function __construct()
    {
        $this->listesMateriel = new ArrayCollection();
        $this->preparationsMateriel = new ArrayCollection();
    }

    // Retourne l'identifiant du matériel chirurgical. Dépendances utilisées : aucune dépendance de service directe.
    public function getId(): ?int
    {
        return $this->id;
    }

    // Retourne l'intitulé du matériel chirurgical. Dépendances utilisées : aucune dépendance de service directe.
    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    // Met à jour l'intitulé du matériel chirurgical. Dépendances utilisées : aucune dépendance de service directe.
    public function setIntitule(string $intitule): static
    {
        $this->intitule = trim($intitule);

        return $this;
    }

    // Retourne l'adresse du matériel chirurgical. Dépendances utilisées : aucune dépendance de service directe.
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    // Met à jour l'adresse du matériel chirurgical. Dépendances utilisées : aucune dépendance de service directe.
    public function setAdresse(?string $adresse): static
    {
        $adresse = null === $adresse ? '' : trim($adresse);
        $this->adresse = '' === $adresse ? null : $adresse;

        return $this;
    }

    // Retourne le type de matériel du matériel chirurgical. Dépendances utilisées : aucune dépendance de service directe.
    public function getTypeMateriel(): ?string
    {
        return $this->typeMateriel;
    }

    // Met à jour le type de matériel du matériel chirurgical. Dépendances utilisées : aucune dépendance de service directe.
    public function setTypeMateriel(?string $typeMateriel): static
    {
        $typeMateriel = null === $typeMateriel ? '' : trim($typeMateriel);
        $this->typeMateriel = '' === $typeMateriel ? null : $typeMateriel;

        return $this;
    }

    // Retourne la spécialité du matériel chirurgical. Dépendances utilisées : Specialite.
    public function getSpecialite(): ?Specialite
    {
        return $this->specialite;
    }

    // Met à jour la spécialité du matériel chirurgical. Dépendances utilisées : Specialite.
    public function setSpecialite(?Specialite $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    // Retourne les listes de matériel du matériel chirurgical. Dépendances utilisées : Collection.
    /**
     * @return Collection<int, ListeMateriel>
     */
    public function getListesMateriel(): Collection
    {
        return $this->listesMateriel;
    }

    // Ajoute la liste de matériel au matériel chirurgical. Dépendances utilisées : Collection, ListeMateriel.
    public function addListeMateriel(ListeMateriel $listeMateriel): static
    {
        if (!$this->listesMateriel->contains($listeMateriel)) {
            $this->listesMateriel->add($listeMateriel);
            $listeMateriel->addMateriel($this);
        }

        return $this;
    }

    // Retire la liste de matériel du matériel chirurgical. Dépendances utilisées : Collection, ListeMateriel.
    public function removeListeMateriel(ListeMateriel $listeMateriel): static
    {
        if ($this->listesMateriel->removeElement($listeMateriel)) {
            $listeMateriel->removeMateriel($this);
        }

        return $this;
    }

    // Retourne les préparations de matériel du matériel chirurgical. Dépendances utilisées : Collection.
    /** @return Collection<int, PreparationMateriel> */
    public function getPreparationsMateriel(): Collection
    {
        return $this->preparationsMateriel;
    }

    // Ajoute la préparation du matériel au matériel chirurgical. Dépendances utilisées : Collection, PreparationMateriel.
    public function addPreparationMateriel(PreparationMateriel $preparation): static
    {
        if (!$this->preparationsMateriel->contains($preparation)) {
            $this->preparationsMateriel->add($preparation);
            $preparation->setMateriel($this);
        }

        return $this;
    }

    // Retire la préparation du matériel du matériel chirurgical. Dépendances utilisées : Collection, PreparationMateriel.
    public function removePreparationMateriel(PreparationMateriel $preparation): static
    {
        $this->preparationsMateriel->removeElement($preparation);

        return $this;
    }
}
