<?php

namespace App\DataFixtures;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\Materiel;
use App\Entity\Specialite;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $specialites = [];
        foreach (['Cardiologie', 'Orthopédie', 'Urologie'] as $intitule) {
            $specialite = (new Specialite())->setIntitule($intitule);
            $specialites[$intitule] = $specialite;
            $manager->persist($specialite);
        }

        foreach ([
            ['prenom' => 'Claire', 'nom' => 'Martin', 'specialite' => 'Cardiologie'],
            ['prenom' => 'Nicolas', 'nom' => 'Bernard', 'specialite' => 'Orthopédie'],
            ['prenom' => 'Sophie', 'nom' => 'Robert', 'specialite' => 'Urologie'],
        ] as $data) {
            $manager->persist(
                (new Chirurgien())
                    ->setPrenom($data['prenom'])
                    ->setNom($data['nom'])
                    ->setSpecialite($specialites[$data['specialite']]),
            );
        }

        foreach ([
            ['intitule' => 'Pontage coronarien', 'specialite' => 'Cardiologie'],
            ['intitule' => 'Prothèse totale de hanche', 'specialite' => 'Orthopédie'],
            ['intitule' => 'Urétéroscopie', 'specialite' => 'Urologie'],
        ] as $data) {
            $manager->persist(
                (new ChirurgieModele())
                    ->setIntitule($data['intitule'])
                    ->setSpecialite($specialites[$data['specialite']]),
            );
        }

        foreach ([
            ['intitule' => 'Boîte de sternotomie', 'adresse' => 'Arsenal A-01', 'type' => 'Instrumentation', 'specialite' => 'Cardiologie'],
            ['intitule' => 'Moteur orthopédique', 'adresse' => 'Arsenal B-04', 'type' => 'Équipement', 'specialite' => 'Orthopédie'],
            ['intitule' => 'Urétéroscope', 'adresse' => 'Arsenal C-02', 'type' => 'Endoscopie', 'specialite' => 'Urologie'],
        ] as $data) {
            $manager->persist(
                (new Materiel())
                    ->setIntitule($data['intitule'])
                    ->setAdresse($data['adresse'])
                    ->setTypeMateriel($data['type'])
                    ->setSpecialite($specialites[$data['specialite']]),
            );
        }

        $manager->flush();
    }
}
