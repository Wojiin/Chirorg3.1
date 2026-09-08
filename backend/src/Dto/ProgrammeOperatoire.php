<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\State\ProgrammeOperatoireProvider;
use App\State\ProgrammeOrdreProcessor;
use App\State\ProgrammePlanificationProcessor;

#[ApiResource(operations: [
    new GetCollection(uriTemplate: '/programmes-operatoires', provider: ProgrammeOperatoireProvider::class, security: "is_granted('ROLE_USER')", paginationEnabled: false),
    new Get(uriTemplate: '/programmes-operatoires/{date}/{salle}/{chirurgien}', provider: ProgrammeOperatoireProvider::class, security: "is_granted('ROLE_USER')"),
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
