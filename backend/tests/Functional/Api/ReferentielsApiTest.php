<?php

namespace App\Tests\Functional\Api;

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

        $client->request('DELETE', $specialite);
        self::assertResponseStatusCodeSame(409);

        $client->request('DELETE', $chirurgien);
        self::assertResponseStatusCodeSame(409);

        $client->request('GET', $chirurgieModele.'/fiches-techniques');
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['totalItems' => 1]);

        $client->request('GET', $chirurgien.'/listes-materiel');
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['totalItems' => 1]);

        foreach ([$listeMateriel, $ficheTechnique, $materiel, $autreMateriel, $chirurgien, $chirurgieModele, $specialite, $autreSpecialite] as $iri) {
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
