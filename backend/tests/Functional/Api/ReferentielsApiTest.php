<?php

namespace App\Tests\Functional\Api;

use App\Entity\Specialite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ReferentielsApiTest extends AuthenticatedApiTestCase
{
    public function testCompleteReferenceDataWorkflow(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur();
        $this->useBearerToken($client, $this->login($client, $utilisateur, $password));
        $suffix = bin2hex(random_bytes(4));

        $client->request('POST', '/api/specialites', ['json' => ['intitule' => '   ']]);
        self::assertResponseStatusCodeSame(422);

        $specialite = self::iri($client->request('POST', '/api/specialites', [
            'json' => ['intitule' => 'Cardiologie '.$suffix],
        ]));
        self::assertResponseStatusCodeSame(201);

        $autreSpecialite = self::iri($client->request('POST', '/api/specialites', [
            'json' => ['intitule' => 'Urologie '.$suffix],
        ]));
        self::assertResponseStatusCodeSame(201);

        $chirurgien = self::iri($client->request('POST', '/api/chirurgiens', [
            'json' => [
                'prenom' => 'Claire',
                'nom' => 'Martin '.$suffix,
                'specialite' => $specialite,
            ],
        ]));
        self::assertResponseStatusCodeSame(201);

        $materiel = self::iri($client->request('POST', '/api/materiels', [
            'json' => [
                'intitule' => 'Boîte de sternotomie '.$suffix,
                'adresse' => 'Arsenal A-01',
                'typeMateriel' => 'Instrumentation',
                'specialite' => $specialite,
            ],
        ]));
        self::assertResponseStatusCodeSame(201);

        $autreMateriel = self::iri($client->request('POST', '/api/materiels', [
            'json' => [
                'intitule' => 'Urétéroscope '.$suffix,
                'specialite' => $autreSpecialite,
            ],
        ]));
        self::assertResponseStatusCodeSame(201);

        $chirurgieModele = self::iri($client->request('POST', '/api/chirurgie-modeles', [
            'json' => [
                'intitule' => 'Pontage coronarien '.$suffix,
                'specialite' => $specialite,
            ],
        ]));
        self::assertResponseStatusCodeSame(201);

        $ficheTechnique = self::iri($client->request('POST', '/api/fiches-techniques', [
            'json' => [
                'titre' => 'Installation',
                'description' => 'Installer le patient.',
                'ordre' => 0,
                'chirurgieModele' => $chirurgieModele,
            ],
        ]));
        self::assertResponseStatusCodeSame(201);

        $client->request('POST', '/api/listes-materiel', [
            'json' => [
                'intitule' => 'Liste incohérente',
                'chirurgien' => $chirurgien,
                'chirurgieModele' => $chirurgieModele,
                'materiels' => [$autreMateriel],
            ],
        ]);
        self::assertResponseStatusCodeSame(422);

        $listeMateriel = self::iri($client->request('POST', '/api/listes-materiel', [
            'json' => [
                'intitule' => 'Standard pontage '.$suffix,
                'chirurgien' => $chirurgien,
                'chirurgieModele' => $chirurgieModele,
                'materiels' => [$materiel],
            ],
        ]));
        self::assertResponseStatusCodeSame(201);

        foreach ([
            [$specialite, ['intitule' => 'Cardiologie adulte '.$suffix]],
            [$chirurgien, ['nom' => 'Martin-Lefèvre '.$suffix]],
            [$materiel, ['adresse' => 'Arsenal A-02']],
            [$chirurgieModele, ['intitule' => 'Pontage coronarien standard '.$suffix]],
            [$ficheTechnique, ['description' => 'Installer puis contrôler le patient.']],
            [$listeMateriel, ['intitule' => 'Standard pontage complet '.$suffix]],
        ] as [$iri, $payload]) {
            $client->request('PATCH', $iri, [
                'headers' => ['content-type' => 'application/merge-patch+json'],
                'json' => $payload,
            ]);
            self::assertResponseIsSuccessful();
            $client->request('GET', $iri);
            self::assertResponseStatusCodeSame(200);
        }

        $paginatedSearch = $client->request('GET', '/api/specialites?'.http_build_query([
            'q' => 'Cardiologie adulte '.$suffix,
            'page' => 1,
            'itemsPerPage' => 1,
        ]));
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['totalItems' => 1]);
        self::assertCount(1, $paginatedSearch->toArray()['member']);

        $client->request('DELETE', $specialite);
        self::assertResponseStatusCodeSame(204);

        foreach ([$chirurgien, $materiel, $chirurgieModele] as $iri) {
            $client->request('GET', $iri);
            self::assertResponseStatusCodeSame(200);
            self::assertJsonContains([
                'specialite' => ['intitule' => Specialite::SANS_SPECIALITE],
            ]);
        }

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $defaultSpecialite = $entityManager->getRepository(Specialite::class)->findOneBy([
            'intitule' => Specialite::SANS_SPECIALITE,
        ]);
        self::assertInstanceOf(Specialite::class, $defaultSpecialite);

        $visibleSpecialites = $client->request('GET', '/api/specialites?'.http_build_query([
            'masquerSansSpecialite' => 'true',
            'itemsPerPage' => 100,
        ]));
        self::assertResponseIsSuccessful();
        self::assertNotContains(
            Specialite::SANS_SPECIALITE,
            array_column($visibleSpecialites->toArray()['member'], 'intitule'),
        );

        $client->request('PATCH', '/api/specialites/'.$defaultSpecialite->getId(), [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => ['intitule' => 'Spécialité renommée interdite'],
        ]);
        self::assertResponseStatusCodeSame(409);
        self::assertJsonContains([
            'detail' => 'La spécialité « Sans spécialité » ne peut pas être modifiée.',
        ]);

        $client->request('DELETE', '/api/specialites/'.$defaultSpecialite->getId());
        self::assertResponseStatusCodeSame(409);
        self::assertJsonContains([
            'detail' => 'La spécialité « Sans spécialité » ne peut pas être supprimée.',
        ]);

        $client->request('DELETE', $chirurgien);
        self::assertResponseStatusCodeSame(409);

        $client->request('GET', $chirurgieModele.'/fiches-techniques');
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['totalItems' => 1]);

        foreach ([$listeMateriel, $ficheTechnique, $materiel, $autreMateriel, $chirurgien, $chirurgieModele, $autreSpecialite] as $iri) {
            $client->request('DELETE', $iri);
            self::assertResponseStatusCodeSame(204);
        }

        $client->request('POST', '/api/auth/logout');
        self::assertResponseIsSuccessful();
        $this->removeUtilisateur($utilisateur);
    }

    private static function iri(ResponseInterface $response): string
    {
        $data = $response->toArray();

        self::assertArrayHasKey('@id', $data);
        self::assertIsString($data['@id']);

        return $data['@id'];
    }
}
