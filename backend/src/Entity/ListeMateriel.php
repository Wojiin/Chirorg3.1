<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\ExactFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\Error\ErrorMessage;
use App\Repository\ListeMaterielRepository;
use App\State\ReferenceDeleteProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ListeMaterielRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_liste_chirurgien_modele', columns: ['chirurgien_id', 'chirurgie_modele_id'])]
#[UniqueEntity(fields: ['chirurgien', 'chirurgieModele'], message: ErrorMessage::LISTE_MATERIEL_ALREADY_EXISTS)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/listes-materiel',
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['liste_materiel:list']],
            parameters: [
                'chirurgien' => new QueryParameter(property: 'chirurgien', filter: new ExactFilter()),
                'chirurgieModele' => new QueryParameter(property: 'chirurgieModele', filter: new ExactFilter()),
                'specialite' => new QueryParameter(property: 'chirurgieModele.specialite', filter: new ExactFilter()),
            ],
        ),
        new GetCollection(
            uriTemplate: '/chirurgiens/{id}/listes-materiel',
            uriVariables: ['id' => new Link(fromClass: Chirurgien::class, toProperty: 'chirurgien')],
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['liste_materiel:read']],
        ),
        new GetCollection(
            uriTemplate: '/chirurgie-modeles/{id}/listes-materiel',
            uriVariables: ['id' => new Link(fromClass: ChirurgieModele::class, toProperty: 'chirurgieModele')],
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['liste_materiel:read']],
        ),
        new Get(uriTemplate: '/listes-materiel/{id}', security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['liste_materiel:read']]),
        new Post(
            uriTemplate: '/listes-materiel',
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['liste_materiel:read']],
            denormalizationContext: ['groups' => ['liste_materiel:write']],
        ),
        new Patch(
            uriTemplate: '/listes-materiel/{id}',
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['liste_materiel:read']],
            denormalizationContext: ['groups' => ['liste_materiel:write']],
        ),
        new Delete(uriTemplate: '/listes-materiel/{id}', security: "is_granted('ROLE_ADMIN')", processor: ReferenceDeleteProcessor::class),
    ],
    order: ['intitule' => 'ASC'],
)]
class ListeMateriel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['liste_materiel:list', 'liste_materiel:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
    #[Assert\Length(max: 150, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
    #[Groups(['liste_materiel:list', 'liste_materiel:read', 'liste_materiel:write'])]
    private ?string $intitule = null;

    #[ORM\ManyToOne(inversedBy: 'listesMateriel')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[Assert\NotNull(message: ErrorMessage::REQUIRED_FIELD)]
    #[Groups(['liste_materiel:list', 'liste_materiel:read', 'liste_materiel:write'])]
    private ?Chirurgien $chirurgien = null;

    #[ORM\ManyToOne(inversedBy: 'listesMateriel')]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[Assert\NotNull(message: ErrorMessage::REQUIRED_FIELD)]
    #[Groups(['liste_materiel:list', 'liste_materiel:read', 'liste_materiel:write'])]
    private ?ChirurgieModele $chirurgieModele = null;

    /**
     * @var Collection<int, Materiel>
     */
    #[ORM\ManyToMany(targetEntity: Materiel::class, inversedBy: 'listesMateriel')]
    #[ORM\JoinTable(name: 'liste_materiel_materiel')]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[Assert\Count(min: 1, minMessage: ErrorMessage::LISTE_MATERIEL_REQUIRES_ITEM)]
    #[Groups(['liste_materiel:read', 'liste_materiel:write'])]
    private Collection $materiels;

    public function __construct()
    {
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

    public function getChirurgien(): ?Chirurgien
    {
        return $this->chirurgien;
    }

    public function setChirurgien(?Chirurgien $chirurgien): static
    {
        $this->chirurgien = $chirurgien;

        return $this;
    }

    public function getChirurgieModele(): ?ChirurgieModele
    {
        return $this->chirurgieModele;
    }

    public function setChirurgieModele(?ChirurgieModele $chirurgieModele): static
    {
        $this->chirurgieModele = $chirurgieModele;

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
            $materiel->addListeMateriel($this);
        }

        return $this;
    }

    public function removeMateriel(Materiel $materiel): static
    {
        if ($this->materiels->removeElement($materiel)) {
            $materiel->removeListeMateriel($this);
        }

        return $this;
    }

    #[Assert\Callback]
    public function validateSpecialites(ExecutionContextInterface $context): void
    {
        $specialite = $this->chirurgien?->getSpecialite();
        if (null === $specialite) {
            return;
        }

        if (null !== $this->chirurgieModele && $this->chirurgieModele->getSpecialite() !== $specialite) {
            $context->buildViolation(ErrorMessage::CHIRURGIE_MODELE_SPECIALITE_MISMATCH)
                ->atPath('chirurgieModele')
                ->addViolation();
        }

        foreach ($this->materiels as $materiel) {
            if ($materiel->getSpecialite() !== $specialite) {
                $context->buildViolation(ErrorMessage::MATERIEL_SPECIALITE_MISMATCH)
                    ->atPath('materiels')
                    ->addViolation();

                break;
            }
        }
    }
}
