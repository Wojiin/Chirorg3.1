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
use App\Repository\ChirurgienRepository;
use App\State\ReferenceDeleteProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChirurgienRepository::class)]
#[ORM\Index(name: 'idx_chirurgien_nom', columns: ['nom'])]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['chirurgien:list']],
            parameters: [
                'nom' => new QueryParameter(property: 'nom', filter: new PartialSearchFilter()),
                'prenom' => new QueryParameter(property: 'prenom', filter: new PartialSearchFilter()),
                'specialite' => new QueryParameter(property: 'specialite', filter: new ExactFilter()),
            ],
        ),
        new Get(normalizationContext: ['groups' => ['chirurgien:read']]),
        new Post(
            normalizationContext: ['groups' => ['chirurgien:read']],
            denormalizationContext: ['groups' => ['chirurgien:write']],
        ),
        new Patch(
            normalizationContext: ['groups' => ['chirurgien:read']],
            denormalizationContext: ['groups' => ['chirurgien:write']],
        ),
        new Delete(processor: ReferenceDeleteProcessor::class),
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
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['chirurgien:list', 'chirurgien:read', 'chirurgien:write'])]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['chirurgien:list', 'chirurgien:read', 'chirurgien:write'])]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'chirurgiens')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['chirurgien:list', 'chirurgien:read', 'chirurgien:write'])]
    private ?Specialite $specialite = null;

    /**
     * @var Collection<int, ListeMateriel>
     */
    #[ORM\OneToMany(targetEntity: ListeMateriel::class, mappedBy: 'chirurgien')]
    private Collection $listesMateriel;

    public function __construct()
    {
        $this->listesMateriel = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = trim($prenom);

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = trim($nom);

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
            $listeMateriel->setChirurgien($this);
        }

        return $this;
    }

    public function removeListeMateriel(ListeMateriel $listeMateriel): static
    {
        if ($this->listesMateriel->removeElement($listeMateriel)) {
            // set the owning side to null (unless already changed)
            if ($listeMateriel->getChirurgien() === $this) {
                $listeMateriel->setChirurgien(null);
            }
        }

        return $this;
    }
}
