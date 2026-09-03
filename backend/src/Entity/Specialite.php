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
    #[Groups(['specialite:list', 'specialite:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['specialite:list', 'specialite:read', 'specialite:write'])]
    private ?string $intitule = null;

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
}
