<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Repository\SpecialiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SpecialiteRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_specialite_intitule', columns: ['intitule'])]
#[UniqueEntity(fields: ['intitule'], message: 'Cette spécialité existe déjà.')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['specialite:list']],
            parameters: [
                'intitule' => new QueryParameter(property: 'intitule', filter: new PartialSearchFilter()),
            ],
        ),
        new Get(normalizationContext: ['groups' => ['specialite:read']]),
        new Post(
            normalizationContext: ['groups' => ['specialite:read']],
            denormalizationContext: ['groups' => ['specialite:write']],
        ),
        new Patch(
            normalizationContext: ['groups' => ['specialite:read']],
            denormalizationContext: ['groups' => ['specialite:write']],
        ),
        new Delete(),
    ],
    order: ['intitule' => 'ASC'],
)]
class Specialite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['specialite:list', 'specialite:read', 'chirurgien:list', 'chirurgien:read', 'materiel:list', 'materiel:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['specialite:list', 'specialite:read', 'specialite:write', 'chirurgien:list', 'chirurgien:read', 'materiel:list', 'materiel:read'])]
    private ?string $intitule = null;

    /**
     * @var Collection<int, Chirurgien>
     */
    #[ORM\OneToMany(targetEntity: Chirurgien::class, mappedBy: 'specialite')]
    private Collection $chirurgiens;

    /**
     * @var Collection<int, Materiel>
     */
    #[ORM\OneToMany(targetEntity: Materiel::class, mappedBy: 'specialite')]
    private Collection $materiels;

    public function __construct()
    {
        $this->chirurgiens = new ArrayCollection();
        $this->materiels = new ArrayCollection();
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

    /**
     * @return Collection<int, Chirurgien>
     */
    public function getChirurgiens(): Collection
    {
        return $this->chirurgiens;
    }

    public function addChirurgien(Chirurgien $chirurgien): static
    {
        if (!$this->chirurgiens->contains($chirurgien)) {
            $this->chirurgiens->add($chirurgien);
            $chirurgien->setSpecialite($this);
        }

        return $this;
    }

    public function removeChirurgien(Chirurgien $chirurgien): static
    {
        if ($this->chirurgiens->removeElement($chirurgien)) {
            // set the owning side to null (unless already changed)
            if ($chirurgien->getSpecialite() === $this) {
                $chirurgien->setSpecialite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Materiel>
     */
    public function getMateriels(): Collection
    {
        return $this->materiels;
    }

    public function addMateriel(Materiel $materiel): static
    {
        if (!$this->materiels->contains($materiel)) {
            $this->materiels->add($materiel);
            $materiel->setSpecialite($this);
        }

        return $this;
    }

    public function removeMateriel(Materiel $materiel): static
    {
        if ($this->materiels->removeElement($materiel)) {
            // set the owning side to null (unless already changed)
            if ($materiel->getSpecialite() === $this) {
                $materiel->setSpecialite(null);
            }
        }

        return $this;
    }
}
