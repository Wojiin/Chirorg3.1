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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uniq_chirurgie_modele_intitule_specialite', columns: ['intitule', 'specialite_id'])]
#[UniqueEntity(fields: ['intitule', 'specialite'], message: ErrorMessage::CHIRURGIE_MODELE_ALREADY_EXISTS)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/chirurgie-modeles',
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['chirurgie_modele:list']],
            parameters: [
                'q' => new QueryParameter(filter: new FreeTextQueryFilter(new OrFilter(new PartialSearchFilter())), properties: ['intitule']),
                'intitule' => new QueryParameter(property: 'intitule', filter: new PartialSearchFilter()),
                'specialite' => new QueryParameter(property: 'specialite', filter: new ExactFilter()),
            ],
        ),
        new Get(uriTemplate: '/chirurgie-modeles/{id}', security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['chirurgie_modele:read']]),
        new Post(
            uriTemplate: '/chirurgie-modeles',
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['chirurgie_modele:read']],
            denormalizationContext: ['groups' => ['chirurgie_modele:write']],
        ),
        new Patch(
            uriTemplate: '/chirurgie-modeles/{id}',
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['chirurgie_modele:read']],
            denormalizationContext: ['groups' => ['chirurgie_modele:write']],
        ),
        new Delete(uriTemplate: '/chirurgie-modeles/{id}', security: "is_granted('ROLE_ADMIN')", processor: ReferenceDeleteProcessor::class),
    ],
    order: ['intitule' => 'ASC'],
)]
class ChirurgieModele
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['chirurgie_modele:list', 'chirurgie_modele:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
    #[Assert\Length(max: 150, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
    #[Groups(['chirurgie_modele:list', 'chirurgie_modele:read', 'chirurgie_modele:write'])]
    private ?string $intitule = null;

    #[ORM\ManyToOne(inversedBy: 'chirurgiesModeles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: ErrorMessage::REQUIRED_FIELD)]
    #[Groups(['chirurgie_modele:list', 'chirurgie_modele:read', 'chirurgie_modele:write'])]
    private ?Specialite $specialite = null;

    /**
     * @var Collection<int, FicheTechnique>
     */
    #[ORM\OneToMany(targetEntity: FicheTechnique::class, mappedBy: 'chirurgieModele', orphanRemoval: true)]
    private Collection $fichesTechniques;

    /**
     * @var Collection<int, ListeMateriel>
     */
    #[ORM\OneToMany(targetEntity: ListeMateriel::class, mappedBy: 'chirurgieModele')]
    private Collection $listesMateriel;

    /** @var Collection<int, ChirurgiePlanifiee> */
    #[ORM\OneToMany(targetEntity: ChirurgiePlanifiee::class, mappedBy: 'chirurgieModele')]
    private Collection $chirurgiesPlanifiees;

    // Initialise le modèle de chirurgie avec ses fiches techniques, ses listes de matériel et ses chirurgies planifiées. Dépendances utilisées : ArrayCollection, Collection.
    public function __construct()
    {
        $this->fichesTechniques = new ArrayCollection();
        $this->listesMateriel = new ArrayCollection();
        $this->chirurgiesPlanifiees = new ArrayCollection();
    }

    // Retourne l'identifiant de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function getId(): ?int
    {
        return $this->id;
    }

    // Retourne l'intitulé de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    // Met à jour l'intitulé de la chirurgie planifiée. Dépendances utilisées : aucune dépendance de service directe.
    public function setIntitule(string $intitule): static
    {
        $this->intitule = trim($intitule);

        return $this;
    }

    // Retourne la spécialité de la chirurgie planifiée. Dépendances utilisées : Specialite.
    public function getSpecialite(): ?Specialite
    {
        return $this->specialite;
    }

    // Met à jour la spécialité de la chirurgie planifiée. Dépendances utilisées : Specialite.
    public function setSpecialite(?Specialite $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    // Retourne les fiches techniques de la chirurgie planifiée. Dépendances utilisées : Collection.
    /**
     * @return Collection<int, FicheTechnique>
     */
    public function getFichesTechniques(): Collection
    {
        return $this->fichesTechniques;
    }

    // Ajoute la fiche technique à la chirurgie planifiée. Dépendances utilisées : Collection, FicheTechnique.
    public function addFicheTechnique(FicheTechnique $ficheTechnique): static
    {
        if (!$this->fichesTechniques->contains($ficheTechnique)) {
            $this->fichesTechniques->add($ficheTechnique);
            $ficheTechnique->setChirurgieModele($this);
        }

        return $this;
    }

    // Retire la fiche technique de la chirurgie planifiée. Dépendances utilisées : Collection, FicheTechnique.
    public function removeFicheTechnique(FicheTechnique $ficheTechnique): static
    {
        if ($this->fichesTechniques->removeElement($ficheTechnique)) {
            // Détache la fiche technique du modèle de chirurgie si elle pointe encore vers celui-ci.
            if ($ficheTechnique->getChirurgieModele() === $this) {
                $ficheTechnique->setChirurgieModele(null);
            }
        }

        return $this;
    }

    // Retourne les listes de matériel de la chirurgie planifiée. Dépendances utilisées : Collection.
    /**
     * @return Collection<int, ListeMateriel>
     */
    public function getListesMateriel(): Collection
    {
        return $this->listesMateriel;
    }

    // Ajoute la liste de matériel à la chirurgie planifiée. Dépendances utilisées : Collection, ListeMateriel.
    public function addListeMateriel(ListeMateriel $listeMateriel): static
    {
        if (!$this->listesMateriel->contains($listeMateriel)) {
            $this->listesMateriel->add($listeMateriel);
            $listeMateriel->setChirurgieModele($this);
        }

        return $this;
    }

    // Retire la liste de matériel de la chirurgie planifiée. Dépendances utilisées : Collection, ListeMateriel.
    public function removeListeMateriel(ListeMateriel $listeMateriel): static
    {
        if ($this->listesMateriel->removeElement($listeMateriel)) {
            // Détache la liste de matériel du modèle de chirurgie si elle pointe encore vers celui-ci.
            if ($listeMateriel->getChirurgieModele() === $this) {
                $listeMateriel->setChirurgieModele(null);
            }
        }

        return $this;
    }

    // Retourne les chirurgies planifiées de la chirurgie planifiée. Dépendances utilisées : Collection.
    /** @return Collection<int, ChirurgiePlanifiee> */
    public function getChirurgiesPlanifiees(): Collection
    {
        return $this->chirurgiesPlanifiees;
    }

    // Ajoute la chirurgie planifiée à la chirurgie planifiée. Dépendances utilisées : ChirurgiePlanifiee, Collection.
    public function addChirurgiePlanifiee(ChirurgiePlanifiee $chirurgie): static
    {
        if (!$this->chirurgiesPlanifiees->contains($chirurgie)) {
            $this->chirurgiesPlanifiees->add($chirurgie);
            $chirurgie->setChirurgieModele($this);
        }

        return $this;
    }

    // Retire la chirurgie planifiée de la chirurgie planifiée. Dépendances utilisées : ChirurgiePlanifiee, Collection.
    public function removeChirurgiePlanifiee(ChirurgiePlanifiee $chirurgie): static
    {
        $this->chirurgiesPlanifiees->removeElement($chirurgie);

        return $this;
    }
}
