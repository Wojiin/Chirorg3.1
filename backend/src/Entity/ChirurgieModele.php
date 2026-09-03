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
use App\Repository\ChirurgieModeleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChirurgieModeleRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_chirurgie_modele_intitule_specialite', columns: ['intitule', 'specialite_id'])]
#[UniqueEntity(fields: ['intitule', 'specialite'], message: 'Cette chirurgie modèle existe déjà pour cette spécialité.')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['chirurgie_modele:list']],
            parameters: [
                'intitule' => new QueryParameter(property: 'intitule', filter: new PartialSearchFilter()),
                'specialite' => new QueryParameter(property: 'specialite', filter: new ExactFilter()),
            ],
        ),
        new Get(normalizationContext: ['groups' => ['chirurgie_modele:read']]),
        new Post(
            normalizationContext: ['groups' => ['chirurgie_modele:read']],
            denormalizationContext: ['groups' => ['chirurgie_modele:write']],
        ),
        new Patch(
            normalizationContext: ['groups' => ['chirurgie_modele:read']],
            denormalizationContext: ['groups' => ['chirurgie_modele:write']],
        ),
        new Delete(),
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
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['chirurgie_modele:list', 'chirurgie_modele:read', 'chirurgie_modele:write'])]
    private ?string $intitule = null;

    #[ORM\ManyToOne(inversedBy: 'chirurgiesModeles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['chirurgie_modele:list', 'chirurgie_modele:read', 'chirurgie_modele:write'])]
    private ?Specialite $specialite = null;

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
}
