<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\FicheTechniqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FicheTechniqueRepository::class)]
#[Assert\Expression(
    expression: 'this.getDescription() !== null or this.getLienImage() !== null',
    message: 'Une fiche technique doit contenir une description, une image ou les deux.',
)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/fiches-techniques', normalizationContext: ['groups' => ['fiche_technique:list']]),
        new GetCollection(
            uriTemplate: '/chirurgie-modeles/{id}/fiches-techniques',
            uriVariables: ['id' => new Link(fromClass: ChirurgieModele::class, toProperty: 'chirurgieModele')],
            normalizationContext: ['groups' => ['fiche_technique:read']],
            order: ['ordre' => 'ASC'],
        ),
        new Get(uriTemplate: '/fiches-techniques/{id}', normalizationContext: ['groups' => ['fiche_technique:read']]),
        new Post(
            uriTemplate: '/fiches-techniques',
            normalizationContext: ['groups' => ['fiche_technique:read']],
            denormalizationContext: ['groups' => ['fiche_technique:write']],
        ),
        new Patch(
            uriTemplate: '/fiches-techniques/{id}',
            normalizationContext: ['groups' => ['fiche_technique:read']],
            denormalizationContext: ['groups' => ['fiche_technique:write']],
        ),
        new Delete(uriTemplate: '/fiches-techniques/{id}'),
    ],
    order: ['ordre' => 'ASC'],
)]
class FicheTechnique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['fiche_technique:list', 'fiche_technique:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['fiche_technique:list', 'fiche_technique:read', 'fiche_technique:write'])]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['fiche_technique:read', 'fiche_technique:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Regex(
        pattern: '/^\/uploads\/fiches-techniques\/[A-Za-z0-9._-]+$/',
        message: 'L’image doit provenir du service de téléversement ChirOrg.',
    )]
    #[Groups(['fiche_technique:read', 'fiche_technique:write'])]
    private ?string $lienImage = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Groups(['fiche_technique:list', 'fiche_technique:read', 'fiche_technique:write'])]
    private ?int $ordre = null;

    #[ORM\ManyToOne(inversedBy: 'fichesTechniques')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['fiche_technique:list', 'fiche_technique:read', 'fiche_technique:write'])]
    private ?ChirurgieModele $chirurgieModele = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = trim($titre);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $description = null === $description ? '' : trim($description);
        $this->description = '' === $description ? null : $description;

        return $this;
    }

    public function getLienImage(): ?string
    {
        return $this->lienImage;
    }

    public function setLienImage(?string $lienImage): static
    {
        $lienImage = null === $lienImage ? '' : trim($lienImage);
        $this->lienImage = '' === $lienImage ? null : $lienImage;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

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
}
