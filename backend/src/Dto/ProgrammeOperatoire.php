<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use App\State\ProgrammeOperatoireProvider;
use App\State\ProgrammeOrdreProcessor;
use App\State\ProgrammePlanificationProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [
    new GetCollection(uriTemplate: '/programmes-operatoires', provider: ProgrammeOperatoireProvider::class, security: "is_granted('ROLE_USER')", paginationEnabled: false, strictQueryParameterValidation: true, parameters: [
        'date' => new QueryParameter(constraints: [new Assert\Date()]),
        'dateDebut' => new QueryParameter(constraints: [new Assert\Date()]),
        'dateFin' => new QueryParameter(constraints: [new Assert\Date()]),
        'salle' => new QueryParameter(constraints: [new Assert\Length(max: 50)]),
        'chirurgien' => new QueryParameter(schema: ['type' => 'integer', 'minimum' => 1], castToNativeType: true, constraints: [new Assert\Positive()]),
    ]),
    new Get(uriTemplate: '/programmes-operatoires/{date}/{salle}/{chirurgien}', provider: ProgrammeOperatoireProvider::class, security: "is_granted('ROLE_USER')"),
    new Get(uriTemplate: '/programmes-operatoires/{date}/{salle}/{chirurgien}/vue-finale', provider: ProgrammeOperatoireProvider::class, security: "is_granted('ROLE_USER')"),
    new Post(uriTemplate: '/programmes-operatoires', input: ProgrammePlanificationInput::class, output: ProgrammePlanificationOutput::class, processor: ProgrammePlanificationProcessor::class, security: "is_granted('ROLE_USER')"),
    new Patch(uriTemplate: '/programmes-operatoires/{date}/{salle}/{chirurgien}/ordre', read: false, input: ProgrammeOrdreInput::class, output: self::class, processor: ProgrammeOrdreProcessor::class, security: "is_granted('ROLE_USER')"),
])]
final readonly class ProgrammeOperatoire
{
    /**
     * @param array{id: int, prenom: string, nom: string} $chirurgien
     * @param list<array<string, mixed>>                  $chirurgies
     */
    public function __construct(public string $id, public string $date, public string $salle, public array $chirurgien, public int $nombreChirurgies, public int $nombreChirurgiesValidees, public array $chirurgies)
    {
    }
}
