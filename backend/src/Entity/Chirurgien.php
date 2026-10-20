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
#[ORM\Index(name: 'idx_chirurgien_nom', columns: ['nom'])]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['chirurgien:list']],
            parameters: [
                'q' => new QueryParameter(filter: new FreeTextQueryFilter(new OrFilter(new PartialSearchFilter())), properties: ['nom', 'prenom']),
                'nom' => new QueryParameter(property: 'nom', filter: new PartialSearchFilter()),
                'prenom' => new QueryParameter(property: 'prenom', filter: new PartialSearchFilter()),
                'specialite' => new QueryParameter(property: 'specialite', filter: new ExactFilter()),
            ],
        ),
        new Get(security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['chirurgien:read']]),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['chirurgien:read']],
            denormalizationContext: ['groups' => ['chirurgien:write']],
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['chirurgien:read']],
            denormalizationContext: ['groups' => ['chirurgien:write']],
        ),
        new Delete(security: "is_granted('ROLE_ADMIN')", processor: ReferenceDeleteProcessor::class),
    ],
    order: ['nom' => 'ASC', 'prenom' => 'ASC'],
)]
class Chirurgien
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['chirurgien:list', 'chirurgien:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
    #[Assert\Length(max: 100, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
    #[Groups(['chirurgien:list', 'chirurgien:read', 'chirurgien:write'])]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
    #[Assert\Length(max: 100, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
    #[Groups(['chirurgien:list', 'chirurgien:read', 'chirurgien:write'])]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'chirurgiens')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: ErrorMessage::REQUIRED_FIELD)]
    #[Groups(['chirurgien:list', 'chirurgien:read', 'chirurgien:write'])]
    private ?Specialite $specialite = null;

    /**
     * @var Collection<int, ListeMateriel>
     */
    #[ORM\OneToMany(targetEntity: ListeMateriel::class, mappedBy: 'chirurgien')]
    private Collection $listesMateriel;

    /** @var Collection<int, ChirurgiePlanifiee> */
    #[ORM\OneToMany(targetEntity: ChirurgiePlanifiee::class, mappedBy: 'chirurgien')]
    private Collection $chirurgiesPlanifiees;

    // Initialise l'entité Chirurgien représentant le chirurgien. Dépendances utilisées : ArrayCollection, Collection.
    public function __construct()
    {
        $this->listesMateriel = new ArrayCollection();
        $this->chirurgiesPlanifiees = new ArrayCollection();
    }

    // Retourne l'identifiant du chirurgien. Dépendances utilisées : aucune dépendance de service directe.
    public function getId(): ?int
    {
        return $this->id;
    }

    // Retourne le prénom du chirurgien. Dépendances utilisées : aucune dépendance de service directe.
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    // Met à jour le prénom du chirurgien. Dépendances utilisées : aucune dépendance de service directe.
    public function setPrenom(string $prenom): static
    {
        $this->prenom = trim($prenom);

        return $this;
    }

    // Retourne le nom du chirurgien. Dépendances utilisées : aucune dépendance de service directe.
    public function getNom(): ?string
    {
        return $this->nom;
    }

    // Met à jour le nom du chirurgien. Dépendances utilisées : aucune dépendance de service directe.
    public function setNom(string $nom): static
    {
        $this->nom = trim($nom);

        return $this;
    }

    // Retourne la spécialité du chirurgien. Dépendances utilisées : Specialite.
    public function getSpecialite(): ?Specialite
    {
        return $this->specialite;
    }

    // Met à jour la spécialité du chirurgien. Dépendances utilisées : Specialite.
    public function setSpecialite(?Specialite $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    // Retourne les listes de matériel du chirurgien. Dépendances utilisées : Collection.
    /**
     * @return Collection<int, ListeMateriel>
     */
    public function getListesMateriel(): Collection
    {
        return $this->listesMateriel;
    }

    // Ajoute la liste de matériel au chirurgien. Dépendances utilisées : Collection, ListeMateriel.
    public function addListeMateriel(ListeMateriel $listeMateriel): static
    {
        if (!$this->listesMateriel->contains($listeMateriel)) {
            $this->listesMateriel->add($listeMateriel);
            $listeMateriel->setChirurgien($this);
        }

        return $this;
    }

    // Retire la liste de matériel du chirurgien. Dépendances utilisées : Collection, ListeMateriel.
    public function removeListeMateriel(ListeMateriel $listeMateriel): static
    {
        if ($this->listesMateriel->removeElement($listeMateriel)) {
            // Détache la liste de matériel du chirurgien si elle pointe encore vers celui-ci.
            if ($listeMateriel->getChirurgien() === $this) {
                $listeMateriel->setChirurgien(null);
            }
        }

        return $this;
    }

    // Retourne les chirurgies planifiées du chirurgien. Dépendances utilisées : Collection.
    /** @return Collection<int, ChirurgiePlanifiee> */
    public function getChirurgiesPlanifiees(): Collection
    {
        return $this->chirurgiesPlanifiees;
    }

    // Ajoute la chirurgie planifiée au chirurgien. Dépendances utilisées : ChirurgiePlanifiee, Collection.
    public function addChirurgiePlanifiee(ChirurgiePlanifiee $chirurgie): static
    {
        if (!$this->chirurgiesPlanifiees->contains($chirurgie)) {
            $this->chirurgiesPlanifiees->add($chirurgie);
            $chirurgie->setChirurgien($this);
        }

        return $this;
    }

    // Retire la chirurgie planifiée du chirurgien. Dépendances utilisées : ChirurgiePlanifiee, Collection.
    public function removeChirurgiePlanifiee(ChirurgiePlanifiee $chirurgie): static
    {
        $this->chirurgiesPlanifiees->removeElement($chirurgie);

        return $this;
    }
}
