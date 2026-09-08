<?php

namespace App\Tests\Functional\Api;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\ChirurgiePlanifiee;
use App\Entity\ListeMateriel;
use App\Entity\Materiel;
use App\Entity\Specialite;
use Doctrine\ORM\EntityManagerInterface;

final class ProgrammeOperatoireApiTest extends AuthenticatedApiTestCase
{
    public function testRoleUserPlansAndReadsAGroupedProgramme(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur(roles: ['ROLE_USER']);
        $this->useBearerToken($client, $this->login($client, $utilisateur, $password));
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $specialite = (new Specialite())->setIntitule('Orthopédie '.bin2hex(random_bytes(4)));
        $chirurgien = (new Chirurgien())->setPrenom('Camille')->setNom('Durand')->setSpecialite($specialite);
        $modele = (new ChirurgieModele())->setIntitule('Prothèse de hanche')->setSpecialite($specialite);
        $materiel = (new Materiel())->setIntitule('Boîte orthopédique')->setSpecialite($specialite);
        $liste = (new ListeMateriel())->setIntitule('Préparation hanche')->setChirurgien($chirurgien)->setChirurgieModele($modele)->addMateriel($materiel);
        foreach ([$specialite, $chirurgien, $modele, $materiel, $liste] as $entity) {
            $entityManager->persist($entity);
        }
        $entityManager->flush();

        $date = new \DateTimeImmutable('tomorrow');
        $response = $client->request('POST', '/api/programmes-operatoires', ['json' => [
            'dateProgrammee' => $date->format('Y-m-d'),
            'salle' => 'Bloc 1',
            'chirurgienId' => $chirurgien->getId(),
            'chirurgieModeleIds' => [$modele->getId()],
        ]]);

        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains(['date' => $date->format('Y-m-d'), 'salle' => 'Bloc 1', 'nombreChirurgies' => 1]);
        $payload = $response->toArray();
        self::assertCount(1, $payload['chirurgies'][0]['preparationsMateriel']);

        $chirurgieId = $payload['chirurgies'][0]['id'];
        $preparationId = $payload['chirurgies'][0]['preparationsMateriel'][0]['id'];
        self::assertIsInt($chirurgieId);
        self::assertIsInt($preparationId);

        $client->request('PATCH', '/api/preparations-materiel/'.$preparationId.'/cocher', [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => ['coche' => true],
        ]);
        self::assertResponseIsSuccessful();

        $client->request('POST', '/api/chirurgies-planifiees/'.$chirurgieId.'/validation');
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['valide' => true]);

        $client->request('GET', sprintf('/api/programmes-operatoires/%s/Bloc%%201/%d', $date->format('Y-m-d'), $chirurgien->getId()));
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['nombreChirurgies' => 1, 'nombreChirurgiesValidees' => 1]);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $chirurgie = $entityManager->find(ChirurgiePlanifiee::class, $chirurgieId);
        if (null !== $chirurgie) {
            $entityManager->remove($chirurgie);
        }
        foreach ([$liste, $materiel, $modele, $chirurgien, $specialite, $utilisateur] as $entity) {
            $managed = $entityManager->find($entity::class, $entity->getId());
            if (null !== $managed) {
                $entityManager->remove($managed);
            }
        }
        $entityManager->flush();
    }
}
