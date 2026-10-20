<?php

namespace App\Entity;

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
use App\ApiFilter\MasquerSpecialiteParDefautFilter;
use App\Error\ErrorMessage;
use App\Repository\SpecialiteRepository;
use App\State\SpecialiteDeleteProcessor;
use App\State\SpecialiteWriteProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SpecialiteRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_specialite_intitule', columns: ['intitule'])]
#[UniqueEntity(fields: ['intitule'], message: ErrorMessage::SPECIALITE_ALREADY_EXISTS)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['specialite:list']],
            parameters: [
                'q' => new QueryParameter(filter: new FreeTextQueryFilter(new OrFilter(new PartialSearchFilter())), properties: ['intitule']),
                'intitule' => new QueryParameter(property: 'intitule', filter: new PartialSearchFilter()),
                'masquerSansSpecialite' => new QueryParameter(
                    schema: ['type' => 'boolean'],
                    description: 'Masque la spécialité technique réservée au repli des références supprimées.',
                    filter: new MasquerSpecialiteParDefautFilter(),
                    castToNativeType: true,
                ),
            ],
        ),
        new Get(security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['specialite:read']]),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['specialite:read']],
            denormalizationContext: ['groups' => ['specialite:write']],
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['specialite:read']],
            denormalizationContext: ['groups' => ['specialite:write']],
            processor: SpecialiteWriteProcessor::class,
        ),
        new Delete(security: "is_granted('ROLE_ADMIN')", processor: SpecialiteDeleteProcessor::class),
    ],
    order: ['intitule' => 'ASC'],
)]
class Specialite
{
    public const SANS_SPECIALITE = 'Sans spécialité';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['specialite:list', 'specialite:read', 'chirurgien:list', 'chirurgien:read', 'materiel:list', 'materiel:read', 'chirurgie_modele:list', 'chirurgie_modele:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
    #[Assert\Length(max: 100, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
    #[Groups(['specialite:list', 'specialite:read', 'specialite:write', 'chirurgien:list', 'chirurgien:read', 'materiel:list', 'materiel:read', 'chirurgie_modele:list', 'chirurgie_modele:read'])]
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

    /**
     * @var Collection<int, ChirurgieModele>
     */
    #[ORM\OneToMany(targetEntity: ChirurgieModele::class, mappedBy: 'specialite')]
    private Collection $chirurgiesModeles;

    // Initialise l'entité Specialite représentant la spécialité. Dépendances utilisées : ArrayCollection, Collection.
    public function __construct()
    {
        $this->chirurgiens = new ArrayCollection();
        $this->materiels = new ArrayCollection();
        $this->chirurgiesModeles = new ArrayCollection();
    }

    // Retourne l'identifiant de la spécialité. Dépendances utilisées : aucune dépendance de service directe.
    public function getId(): ?int
    {
        return $this->id;
    }

    // Retourne l'intitulé de la spécialité. Dépendances utilisées : aucune dépendance de service directe.
    public function getIntitule(): ?string
    {
        return $this->intitule;
    }

    // Met à jour l'intitulé de la spécialité. Dépendances utilisées : aucune dépendance de service directe.
    public function setIntitule(string $intitule): static
    {
        $this->intitule = trim($intitule);

        return $this;
    }

    // Retourne les chirurgiens de la spécialité. Dépendances utilisées : Collection.
    /**
     * @return Collection<int, Chirurgien>
     */
    public function getChirurgiens(): Collection
    {
        return $this->chirurgiens;
    }

    // Ajoute le chirurgien à la spécialité. Dépendances utilisées : Chirurgien, Collection.
    public function addChirurgien(Chirurgien $chirurgien): static
    {
        if (!$this->chirurgiens->contains($chirurgien)) {
            $this->chirurgiens->add($chirurgien);
            $chirurgien->setSpecialite($this);
        }

        return $this;
    }

    // Retire le chirurgien de la spécialité. Dépendances utilisées : Chirurgien, Collection.
    public function removeChirurgien(Chirurgien $chirurgien): static
    {
        if ($this->chirurgiens->removeElement($chirurgien)) {
            // Détache le chirurgien de la spécialité si cette relation est encore active.
            if ($chirurgien->getSpecialite() === $this) {
                $chirurgien->setSpecialite(null);
            }
        }

        return $this;
    }

    // Retourne les matériels chirurgicaux de la spécialité. Dépendances utilisées : Collection.
    /**
     * @return Collection<int, Materiel>
     */
    public function getMateriels(): Collection
    {
        return $this->materiels;
    }

    // Ajoute le matériel chirurgical à la spécialité. Dépendances utilisées : Collection, Materiel.
    public function addMateriel(Materiel $materiel): static
    {
        if (!$this->materiels->contains($materiel)) {
            $this->materiels->add($materiel);
            $materiel->setSpecialite($this);
        }

        return $this;
    }

    // Retire le matériel chirurgical de la spécialité. Dépendances utilisées : Collection, Materiel.
    public function removeMateriel(Materiel $materiel): static
    {
        if ($this->materiels->removeElement($materiel)) {
            // Détache le matériel de la spécialité si cette relation est encore active.
            if ($materiel->getSpecialite() === $this) {
                $materiel->setSpecialite(null);
            }
        }

        return $this;
    }

    // Retourne les modèles de chirurgie de la spécialité. Dépendances utilisées : Collection.
    /**
     * @return Collection<int, ChirurgieModele>
     */
    public function getChirurgiesModeles(): Collection
    {
        return $this->chirurgiesModeles;
    }

    // Ajoute le modèle de chirurgie à la spécialité. Dépendances utilisées : ChirurgieModele, Collection.
    public function addChirurgieModele(ChirurgieModele $chirurgieModele): static
    {
        if (!$this->chirurgiesModeles->contains($chirurgieModele)) {
            $this->chirurgiesModeles->add($chirurgieModele);
            $chirurgieModele->setSpecialite($this);
        }

        return $this;
    }

    // Retire le modèle de chirurgie de la spécialité. Dépendances utilisées : ChirurgieModele, Collection.
    public function removeChirurgieModele(ChirurgieModele $chirurgieModele): static
    {
        if ($this->chirurgiesModeles->removeElement($chirurgieModele)) {
            // Détache le modèle de chirurgie de la spécialité si cette relation est encore active.
            if ($chirurgieModele->getSpecialite() === $this) {
                $chirurgieModele->setSpecialite(null);
            }
        }

        return $this;
    }
}
