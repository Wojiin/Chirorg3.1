<?php

namespace App\DataFixtures;

use App\Entity\Chirurgien;
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

        $manager->flush();
    }
}
