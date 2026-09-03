<?php

namespace App\DataFixtures;

use App\Entity\Specialite;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (['Cardiologie', 'Orthopédie', 'Urologie'] as $intitule) {
            $manager->persist((new Specialite())->setIntitule($intitule));
        }

        $manager->flush();
    }
}
