<?php

namespace App\Tests\Functional\Api;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\ChirurgiePlanifiee;
use App\Entity\FicheTechnique;
use App\Entity\ListeMateriel;
use App\Entity\Materiel;
use App\Entity\Specialite;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

final class ProgrammeOperatoireApiTest extends AuthenticatedApiTestCase
{
    public function testDirectPlanningWritesAreNotExposed(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur(roles: []);
        $this->useBearerToken($client, $this->login($client, $utilisateur, $password));

        $client->request('POST', '/api/chirurgies-planifiees', ['json' => []]);
        self::assertResponseStatusCodeSame(405);
        $client->request('PATCH', '/api/chirurgies-planifiees/1', [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => [],
        ]);
        self::assertResponseStatusCodeSame(405);

        $this->removeUtilisateur($utilisateur);
    }

    public function testPlanningWithoutMaterialListRollsBackEveryWrite(): void
    {
        $client = $this->createApiClient();
        [$utilisateur, $password] = $this->persistUtilisateur(roles: ['ROLE_USER']);
        $this->useBearerToken($client, $this->login($client, $utilisateur, $password));
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $specialite = (new Specialite())->setIntitule('Neurologie '.bin2hex(random_bytes(4)));
        $chirurgien = (new Chirurgien())->setPrenom('Alex')->setNom('Martin')->setSpecialite($specialite);
        $modele = (new ChirurgieModele())->setIntitule('Intervention sans liste')->setSpecialite($specialite);
        foreach ([$specialite, $chirurgien, $modele] as $entity) {
            $entityManager->persist($entity);
        }
        $entityManager->flush();

        $client->request('POST', '/api/programmes-operatoires', ['json' => [
            'dateProgrammee' => (new \DateTimeImmutable('tomorrow'))->format('Y-m-d'),
            'salle' => 'Bloc 2',
            'chirurgienId' => $chirurgien->getId(),
            'chirurgieModeleIds' => [$modele->getId()],
        ]]);
        self::assertResponseStatusCodeSame(422);

        $registry = static::getContainer()->get(ManagerRegistry::class);
        $registry->resetManager();
        $entityManager = $registry->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $managedChirurgien = $entityManager->find(Chirurgien::class, $chirurgien->getId());
        self::assertSame([], $entityManager->getRepository(ChirurgiePlanifiee::class)->findBy(['chirurgien' => $managedChirurgien]));
        foreach ([$modele, $chirurgien, $specialite, $utilisateur] as $entity) {
            $managed = $entityManager->find($entity::class, $entity->getId());
            if (null !== $managed) {
                $entityManager->remove($managed);
            }
        }
        $entityManager->flush();
    }

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
        $fiche = (new FicheTechnique())->setTitre('Installation')->setDescription('Installer le patient.')->setOrdre(1)->setChirurgieModele($modele);
        $liste = (new ListeMateriel())->setIntitule('Préparation hanche')->setChirurgien($chirurgien)->setChirurgieModele($modele)->addMateriel($materiel);
        foreach ([$specialite, $chirurgien, $modele, $materiel, $fiche, $liste] as $entity) {
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

        $client->request('GET', '/api/chirurgies-planifiees/'.$chirurgieId.'/preparation');
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['ordre' => 1, 'nombreChirurgies' => 1, 'etatValidation' => 'EN_PREPARATION', 'progressionPreparation' => ['total' => 1, 'traites' => 0, 'complete' => false]]);

        $nestedPreparations = $client->request('GET', '/api/chirurgies-planifiees/'.$chirurgieId.'/preparations-materiel');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('"id":'.$preparationId, $nestedPreparations->getContent());

        $filteredChirurgies = $client->request('GET', sprintf('/api/chirurgies-planifiees?dateProgrammee=%s&salle=Bloc%%201&chirurgien=%d', $date->format('Y-m-d'), $chirurgien->getId()));
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('"id":'.$chirurgieId, $filteredChirurgies->getContent());

        $client->request('GET', '/api/chirurgies-planifiees/'.$chirurgieId.'/vue-finale');
        self::assertResponseStatusCodeSame(409);

        $client->request('POST', '/api/chirurgies-planifiees/'.$chirurgieId.'/validation');
        self::assertResponseStatusCodeSame(422);

        $client->request('PATCH', '/api/preparations-materiel/'.$preparationId.'/cocher', [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => ['coche' => true, 'absent' => true],
        ]);
        self::assertResponseStatusCodeSame(422);

        $client->request('PATCH', '/api/preparations-materiel/'.$preparationId.'/cocher', [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => ['absent' => true],
        ]);
        self::assertResponseIsSuccessful();

        $client->request('POST', '/api/chirurgies-planifiees/'.$chirurgieId.'/validation');
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['valide' => false]);

        $client->request('GET', '/api/chirurgies-planifiees/'.$chirurgieId.'/preparation');
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['valide' => false, 'etatValidation' => 'VALIDATION_PARTIELLE']);

        $client->request('PATCH', '/api/preparations-materiel/'.$preparationId.'/cocher', [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => ['coche' => true, 'absent' => false],
        ]);
        self::assertResponseIsSuccessful();

        $client->request('POST', '/api/chirurgies-planifiees/'.$chirurgieId.'/validation');
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['valide' => true]);

        $client->request('GET', '/api/chirurgies-planifiees/'.$chirurgieId.'/vue-finale');
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['valide' => true, 'materielsValides' => [['intitule' => $materiel->getIntitule()]], 'ficheTechnique' => [['titre' => 'Installation']]]);

        $client->request('PATCH', '/api/preparations-materiel/'.$preparationId.'/cocher', [
            'headers' => ['content-type' => 'application/merge-patch+json'],
            'json' => ['coche' => false],
        ]);
        self::assertResponseStatusCodeSame(409);

        $client->request('GET', sprintf('/api/programmes-operatoires/%s/Bloc%%201/%d', $date->format('Y-m-d'), $chirurgien->getId()));
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['nombreChirurgies' => 1, 'nombreChirurgiesValidees' => 1]);

        $client->request('GET', sprintf('/api/programmes-operatoires/%s/Bloc%%201/%d/vue-finale', $date->format('Y-m-d'), $chirurgien->getId()));
        self::assertResponseIsSuccessful();
        self::assertJsonContains(['nombreChirurgies' => 1, 'nombreChirurgiesValidees' => 1]);

        $programmeUrl = sprintf('/api/programmes-operatoires/%s/Bloc%%201/%d/ordre', $date->format('Y-m-d'), $chirurgien->getId());
        $client->request('PATCH', $programmeUrl, ['headers' => ['content-type' => 'application/merge-patch+json'], 'json' => ['chirurgieIds' => [$chirurgieId]]]);
        self::assertResponseStatusCodeSame(409);

        $filteredResponse = $client->request('GET', sprintf('/api/programmes-operatoires?dateDebut=%s&dateFin=%s&page=1&itemsPerPage=1', $date->format('Y-m-d'), $date->format('Y-m-d')));
        self::assertResponseIsSuccessful();
        $filteredPayload = $filteredResponse->toArray();
        self::assertGreaterThanOrEqual(1, $filteredPayload['totalItems']);
        self::assertCount(1, $filteredPayload['member']);
        self::assertStringContainsString('"date":"'.$date->format('Y-m-d').'"', $filteredResponse->getContent());
        $client->request('GET', sprintf('/api/programmes-operatoires?dateDebut=%s&dateFin=%s', $date->modify('+1 day')->format('Y-m-d'), $date->format('Y-m-d')));
        self::assertResponseStatusCodeSame(400);

        $client->request('DELETE', '/api/chirurgies-planifiees/'.$chirurgieId);
        self::assertResponseStatusCodeSame(409);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $chirurgie = $entityManager->find(ChirurgiePlanifiee::class, $chirurgieId);
        if (null !== $chirurgie) {
            $entityManager->remove($chirurgie);
        }
        foreach ([$liste, $fiche, $materiel, $modele, $chirurgien, $specialite, $utilisateur] as $entity) {
            $managed = $entityManager->find($entity::class, $entity->getId());
            if (null !== $managed) {
                $entityManager->remove($managed);
            }
        }
        $entityManager->flush();
    }
}
