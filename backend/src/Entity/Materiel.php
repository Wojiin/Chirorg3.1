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
use App\Repository\MaterielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MaterielRepository::class)]
#[ORM\Index(name: 'idx_materiel_intitule', columns: ['intitule'])]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['materiel:list']],
            parameters: [
                'intitule' => new QueryParameter(property: 'intitule', filter: new PartialSearchFilter()),
                'typeMateriel' => new QueryParameter(property: 'typeMateriel', filter: new ExactFilter()),
                'adresse' => new QueryParameter(property: 'adresse', filter: new PartialSearchFilter()),
                'specialite' => new QueryParameter(property: 'specialite', filter: new ExactFilter()),
            ],
        ),
        new Get(normalizationContext: ['groups' => ['materiel:read']]),
        new Post(
            normalizationContext: ['groups' => ['materiel:read']],
            denormalizationContext: ['groups' => ['materiel:write']],
        ),
        new Patch(
            normalizationContext: ['groups' => ['materiel:read']],
            denormalizationContext: ['groups' => ['materiel:write']],
        ),
        new Delete(),
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
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['materiel:list', 'materiel:read', 'materiel:write'])]
    private ?string $intitule = null;

    #[ORM\Column(length: 150, nullable: true)]
    #[Assert\Length(max: 150)]
    #[Groups(['materiel:list', 'materiel:read', 'materiel:write'])]
    private ?string $adresse = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    #[Groups(['materiel:list', 'materiel:read', 'materiel:write'])]
    private ?string $typeMateriel = null;

    #[ORM\ManyToOne(inversedBy: 'materiels')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['materiel:list', 'materiel:read', 'materiel:write'])]
    private ?Specialite $specialite = null;

    /**
     * @var Collection<int, ListeMateriel>
     */
    #[ORM\ManyToMany(targetEntity: ListeMateriel::class, mappedBy: 'materiels')]
    private Collection $listesMateriel;

    public function __construct()
    {
        $this->listesMateriel = new ArrayCollection();
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $adresse = null === $adresse ? '' : trim($adresse);
        $this->adresse = '' === $adresse ? null : $adresse;

        return $this;
    }

    public function getTypeMateriel(): ?string
    {
        return $this->typeMateriel;
    }

    public function setTypeMateriel(?string $typeMateriel): static
    {
        $typeMateriel = null === $typeMateriel ? '' : trim($typeMateriel);
        $this->typeMateriel = '' === $typeMateriel ? null : $typeMateriel;

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
            $listeMateriel->addMateriel($this);
        }

        return $this;
    }

    public function removeListeMateriel(ListeMateriel $listeMateriel): static
    {
        if ($this->listesMateriel->removeElement($listeMateriel)) {
            $listeMateriel->removeMateriel($this);
        }

        return $this;
    }
}
