<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Error\ErrorMessage;
use App\Repository\ChirurgieModeleRepository;
use App\State\ReferenceDeleteProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChirurgieModeleRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_chirurgie_modele_intitule_specialite', columns: ['intitule', 'specialite_id'])]
#[UniqueEntity(fields: ['intitule', 'specialite'], message: ErrorMessage::CHIRURGIE_MODELE_ALREADY_EXISTS)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/chirurgie-modeles',
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['chirurgie_modele:list']],
            parameters: [
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

    public function __construct()
    {
        $this->fichesTechniques = new ArrayCollection();
        $this->listesMateriel = new ArrayCollection();
        $this->chirurgiesPlanifiees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    public function setIntitule(string $intitule): static
    {
        $this->intitule = trim($intitule);

        return $this;
    }

    public function getSpecialite(): ?Specialite
    {
        return $this->specialite;
    }

    public function setSpecialite(?Specialite $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    /**
     * @return Collection<int, FicheTechnique>
     */
    public function getFichesTechniques(): Collection
    {
        return $this->fichesTechniques;
    }

    public function addFicheTechnique(FicheTechnique $ficheTechnique): static
    {
        if (!$this->fichesTechniques->contains($ficheTechnique)) {
            $this->fichesTechniques->add($ficheTechnique);
            $ficheTechnique->setChirurgieModele($this);
        }

        return $this;
    }

    public function removeFicheTechnique(FicheTechnique $ficheTechnique): static
    {
        if ($this->fichesTechniques->removeElement($ficheTechnique)) {
            // set the owning side to null (unless already changed)
            if ($ficheTechnique->getChirurgieModele() === $this) {
                $ficheTechnique->setChirurgieModele(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ListeMateriel>
     */
    public function getListesMateriel(): Collection
    {
        return $this->listesMateriel;
    }

    public function addListeMateriel(ListeMateriel $listeMateriel): static
    {
        if (!$this->listesMateriel->contains($listeMateriel)) {
            $this->listesMateriel->add($listeMateriel);
            $listeMateriel->setChirurgieModele($this);
        }

        return $this;
    }

    public function removeListeMateriel(ListeMateriel $listeMateriel): static
    {
        if ($this->listesMateriel->removeElement($listeMateriel)) {
            // set the owning side to null (unless already changed)
            if ($listeMateriel->getChirurgieModele() === $this) {
                $listeMateriel->setChirurgieModele(null);
            }
        }

        return $this;
    }

    /** @return Collection<int, ChirurgiePlanifiee> */
    public function getChirurgiesPlanifiees(): Collection
    {
        return $this->chirurgiesPlanifiees;
    }

    public function addChirurgiePlanifiee(ChirurgiePlanifiee $chirurgie): static
    {
        if (!$this->chirurgiesPlanifiees->contains($chirurgie)) {
            $this->chirurgiesPlanifiees->add($chirurgie);
            $chirurgie->setChirurgieModele($this);
        }

        return $this;
    }

    public function removeChirurgiePlanifiee(ChirurgiePlanifiee $chirurgie): static
    {
        $this->chirurgiesPlanifiees->removeElement($chirurgie);

        return $this;
    }
}
