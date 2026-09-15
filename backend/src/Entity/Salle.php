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
use App\Error\ErrorMessage;
use App\Repository\SalleRepository;
use App\State\SalleDeleteProcessor;
use App\State\SalleWriteProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_salle_intitule', columns: ['intitule'])]
#[UniqueEntity(fields: ['intitule'], message: ErrorMessage::SALLE_ALREADY_EXISTS)]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ROLE_USER')",
            normalizationContext: ['groups' => ['salle:list']],
            parameters: [
                'q' => new QueryParameter(filter: new FreeTextQueryFilter(new OrFilter(new PartialSearchFilter())), properties: ['intitule']),
                'intitule' => new QueryParameter(property: 'intitule', filter: new PartialSearchFilter()),
            ],
        ),
        new Get(security: "is_granted('ROLE_USER')", normalizationContext: ['groups' => ['salle:read']]),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['salle:read']],
            denormalizationContext: ['groups' => ['salle:write']],
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['salle:read']],
            denormalizationContext: ['groups' => ['salle:write']],
            processor: SalleWriteProcessor::class,
        ),
        new Delete(security: "is_granted('ROLE_ADMIN')", processor: SalleDeleteProcessor::class),
    ],
    order: ['intitule' => 'ASC'],
)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['salle:list', 'salle:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: ErrorMessage::REQUIRED_FIELD)]
    #[Assert\Length(max: 50, maxMessage: ErrorMessage::TEXT_TOO_LONG)]
    #[Groups(['salle:list', 'salle:read', 'salle:write'])]
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
