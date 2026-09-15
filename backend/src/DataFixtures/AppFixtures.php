<?php

namespace App\DataFixtures;

use App\Entity\ChirurgieModele;
use App\Entity\Chirurgien;
use App\Entity\ChirurgiePlanifiee;
use App\Entity\FicheTechnique;
use App\Entity\ListeMateriel;
use App\Entity\Materiel;
use App\Entity\PreparationMateriel;
use App\Entity\Salle;
use App\Entity\Specialite;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** Constitue un jeu de démonstration volumineux, déterministe et cohérent. */
final class AppFixtures extends Fixture
{
    public const ADMIN_EMAIL = 'admin@chirorg.test';
    public const ADMIN_PASSWORD = 'AdminFixture-2026!';
    public const USER_EMAIL = 'user@chirorg.test';
    public const USER_PASSWORD = 'UserFixture-2026!';

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->createUtilisateur(self::ADMIN_EMAIL, ['ROLE_ADMIN'], self::ADMIN_PASSWORD);
        $user = $this->createUtilisateur(self::USER_EMAIL, ['ROLE_USER'], self::USER_PASSWORD);
        $manager->persist($admin);
        $manager->persist($user);

        $salles = ['Salle A', 'Salle B', 'Salle C'];
        foreach ($salles as $intitule) {
            $manager->persist((new Salle())->setIntitule($intitule));
        }

        $specialites = [];
        foreach (['Orthopédie', 'Chirurgie viscérale et digestive', 'Chirurgie générale', 'Traumatologie', 'Urologie', Specialite::SANS_SPECIALITE] as $intitule) {
            $specialite = (new Specialite())->setIntitule($intitule);
            $specialites[] = $specialite;
            $manager->persist($specialite);
        }

        $chirurgiens = $this->createChirurgiens($specialites);
        foreach ($chirurgiens as $chirurgien) {
            $manager->persist($chirurgien);
        }

        $modeles = [];
        $modelesParSpecialite = array_fill(0, 5, []);
        foreach ($this->chirurgieModeleData() as [$intitule, $specialiteIndex]) {
            $modele = (new ChirurgieModele())
                ->setIntitule($intitule)
                ->setSpecialite($specialites[$specialiteIndex]);
            $modeles[] = $modele;
            $modelesParSpecialite[$specialiteIndex][] = $modele;
            $manager->persist($modele);
            foreach ($this->createFichesTechniques($modele, $intitule) as $fiche) {
                $manager->persist($fiche);
            }
        }

        $materiels = [];
        foreach ($this->materielData() as $index => [$intitule, $adresse, $type]) {
            $materiel = (new Materiel())
                ->setIntitule($intitule)
                ->setAdresse($adresse)
                ->setTypeMateriel($type)
                ->setSpecialite($specialites[$index % 5]);
            $materiels[] = $materiel;
            $manager->persist($materiel);
        }

        $listes = [];
        foreach ($chirurgiens as $chirurgienIndex => $chirurgien) {
            $specialiteIndex = $chirurgienIndex % 5;
            foreach ($modelesParSpecialite[$specialiteIndex] as $modeleIndex => $modele) {
                $liste = (new ListeMateriel())
                    ->setIntitule(sprintf('Liste %s %s - %s', $chirurgien->getNom(), $chirurgien->getPrenom(), $modele->getIntitule()))
                    ->setChirurgien($chirurgien)
                    ->setChirurgieModele($modele);
                $materielsCompatibles = array_values(array_filter(
                    $materiels,
                    static fn (Materiel $materiel): bool => $materiel->getSpecialite() === $chirurgien->getSpecialite(),
                ));
                foreach ($this->pickMateriels($materielsCompatibles, $chirurgienIndex, $modeleIndex) as $materiel) {
                    $liste->addMateriel($materiel);
                }
                $listes[$chirurgienIndex.'-'.$modeleIndex] = $liste;
                $manager->persist($liste);
            }
        }

        $chirurgies = [];
        $aujourdhui = new \DateTimeImmutable('today');
        for ($index = 0; $index < 30; ++$index) {
            $groupeIndex = intdiv($index, 2);
            $chirurgienIndex = $groupeIndex % 5;
            $modeleIndex = $index % 4;
            $chirurgie = $this->createChirurgie(
                $aujourdhui->modify(sprintf('+%d day', intdiv($index, 6))),
                $salles[$groupeIndex % count($salles)],
                ($index % 2) + 1,
                $chirurgiens[$chirurgienIndex],
                $modelesParSpecialite[$chirurgienIndex][$modeleIndex],
            );
            $chirurgies[] = $chirurgie;
            $manager->persist($chirurgie);
            foreach ($listes[$chirurgienIndex.'-'.$modeleIndex]->getMateriels() as $materielIndex => $materiel) {
                $preparation = (new PreparationMateriel())
                    ->setChirurgiePlanifiee($chirurgie)
                    ->setMateriel($materiel);
                if (0 === $index || (0 === $index % 5 && $materielIndex < 4)) {
                    $this->markReady($preparation, $user);
                }
                $chirurgie->addPreparationMateriel($preparation);
                $manager->persist($preparation);
            }
        }

        foreach (array_slice($chirurgies, 0, 6) as $chirurgie) {
            foreach ($chirurgie->getPreparationsMateriel() as $preparation) {
                $this->markReady($preparation, $user);
            }
            $chirurgie->setValide(true)->setValideLe(new \DateTimeImmutable())->setValidePar($user);
        }

        $manager->flush();
    }

    /** @param list<string> $roles */
    private function createUtilisateur(string $email, array $roles, string $plainPassword): Utilisateur
    {
        $utilisateur = (new Utilisateur())->setEmail($email)->setRoles($roles)->setActif(true);

        return $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, $plainPassword));
    }

    /** @param list<Specialite> $specialites
     *
     * @return list<Chirurgien>
     */
    private function createChirurgiens(array $specialites): array
    {
        $identites = [
            ['Jean', 'Dupont'], ['Claire', 'Martin'], ['Alain', 'Bernard'], ['Nadia', 'Petit'], ['Hugo', 'Leroy'],
            ['Nicolas', 'Bernard'], ['Lucas', 'Simon'], ['Emma', 'Laurent'], ['Louis', 'Michel'], ['Alice', 'Garcia'],
            ['Thomas', 'David'], ['Julie', 'Bertrand'], ['Nicolas', 'Roux'], ['Sarah', 'Vincent'], ['Marc', 'Fournier'],
            ['Camille', 'Girard'], ['Paul', 'Andre'], ['Léa', 'Mercier'], ['Antoine', 'Blanc'], ['Eva', 'Guerin'],
            ['Maxime', 'Boyer'], ['Chloé', 'Garnier'], ['Julien', 'Chevalier'], ['Manon', 'Francois'], ['Romain', 'Legrand'],
        ];

        return array_map(
            static fn (array $identite, int $index): Chirurgien => (new Chirurgien())
                ->setPrenom($identite[0])
                ->setNom($identite[1])
                ->setSpecialite($specialites[$index % 5]),
            $identites,
            array_keys($identites),
        );
    }

    private function createChirurgie(\DateTimeImmutable $date, string $salle, int $ordre, Chirurgien $chirurgien, ChirurgieModele $modele): ChirurgiePlanifiee
    {
        $now = new \DateTimeImmutable();

        return (new ChirurgiePlanifiee())
            ->setDateProgrammee($date)
            ->setSalle($salle)
            ->setOrdre($ordre)
            ->setChirurgien($chirurgien)
            ->setChirurgieModele($modele)
            ->setCreeLe($now)
            ->setCreePar(self::ADMIN_EMAIL)
            ->setModifieLe($now)
            ->setModifiePar(self::ADMIN_EMAIL);
    }

    private function markReady(PreparationMateriel $preparation, Utilisateur $utilisateur): void
    {
        $preparation->setCoche(true)->setAbsent(false)->setCocheLe(new \DateTimeImmutable())->setCochePar($utilisateur);
    }

    /** @return list<array{string, int}> */
    private function chirurgieModeleData(): array
    {
        return [
            ['Prothèse totale de genou', 0], ['Prothèse totale de hanche', 0], ['Arthroscopie de l’épaule', 0], ['Arthroscopie du genou', 0],
            ['Appendicectomie', 1], ['Cholécystectomie cœlioscopique', 1], ['Hernie inguinale', 1], ['Hémicolectomie droite', 1],
            ['Thyroïdectomie partielle', 2], ['Biopsie ganglionnaire', 2], ['Pose de chambre implantable', 2], ['Cure d’éventration', 2],
            ['Ligamentoplastie du croisé antérieur', 3], ['Ostéosynthèse de cheville', 3], ['Ostéosynthèse de poignet', 3], ['Suture du tendon d’Achille', 3],
            ['Résection transurétrale de prostate', 4], ['Urétéroscopie', 4], ['Néphrectomie', 4], ['Prostatectomie', 4],
        ];
    }

    /** @return list<FicheTechnique> */
    private function createFichesTechniques(ChirurgieModele $modele, string $intitule): array
    {
        $etapes = [
            ['Installation', 'Installer le patient selon le protocole et vérifier les points d’appui.'],
            ['Préparation de salle', 'Contrôler l’aspiration, l’éclairage, l’imagerie et la disponibilité du plateau.'],
            ['Temps opératoire', 'Confirmer les instruments critiques et les consommables spécifiques à l’intervention.'],
        ];

        return array_map(
            static fn (array $etape, int $index): FicheTechnique => (new FicheTechnique())
                ->setTitre($etape[0].' - '.$intitule)
                ->setDescription($etape[1])
                ->setOrdre($index + 1)
                ->setChirurgieModele($modele),
            $etapes,
            array_keys($etapes),
        );
    }

    /** @return list<array{string, string, string}> */
    private function materielData(): array
    {
        $noms = [
            'Scalpel n10', 'Scalpel n15', 'Manche bistouri n3', 'Manche bistouri n4', 'Ciseaux Mayo droits', 'Ciseaux Mayo courbes', 'Ciseaux Metzenbaum', 'Pince Kocher droite', 'Pince Kocher courbe', 'Pince Kelly',
            'Pince Halsted', 'Pince Adson', 'Pince à disséquer', 'Pince anatomique', 'Pince chirurgicale', 'Pince porte-aiguille', 'Écarteur Farabeuf', 'Écarteur Gelpi', 'Écarteur Weitlaner', 'Écarteur abdominal',
            'Valve de Doyen', 'Valve de Richardson', 'Aiguille courbe', 'Aiguille droite', 'Fil résorbable 2-0', 'Fil résorbable 3-0', 'Fil non résorbable 2-0', 'Fil non résorbable 3-0', 'Agrafeuse cutanée', 'Ôte-agrafes',
            'Compresses stériles 10x10', 'Compresses stériles 5x5', 'Champs opératoires', 'Casaque stérile', 'Gants stériles taille 6', 'Gants stériles taille 7', 'Gants stériles taille 8', 'Seringue 10 ml', 'Seringue 20 ml', 'Aiguille injection',
            'Canule aspiration', 'Tuyau aspiration', 'Bocal aspiration', 'Électrode bistouri', 'Plaque bistouri électrique', 'Câble bistouri électrique', 'Poignée lumière stérile', 'Sonde urinaire', 'Poche recueil', 'Drain Redon',
            'Drain aspiratif', 'Lame de drainage', 'Set perfusion', 'Tubulure perfusion', 'Pansement stérile', 'Pansement compressif', 'Bande adhésive', 'Bande élastique', 'Garrot pneumatique', 'Moteur orthopédique',
            'Scie oscillante', 'Foret 2 mm', 'Foret 3 mm', 'Foret 4 mm', 'Broche Kirschner', 'Plaque verrouillée', 'Vis corticale', 'Vis spongieuse', 'Guide de coupe', 'Ancillaire genou',
            'Ancillaire hanche', 'Cotyle essai', 'Tige fémorale essai', 'Râpe fémorale', 'Curette osseuse', 'Maillet orthopédique', 'Ostéotome', 'Rugine', 'Pince à os', 'Caméra arthroscopie',
            'Optique 30 degrés', 'Trocart arthroscopie', 'Shaver', 'Pompe arthroscopie', 'Canule arthroscopie', 'Fil guide', 'Trocart cœlioscopie', 'Optique cœlioscopie', 'Insufflateur', 'Clip applier',
            'Pinces cœlioscopie', 'Sac extraction', 'Ligasure', 'Hemolock', 'Plateau anesthésie', 'Masque oxygène', 'Capteur saturation', 'Couverture chauffante', 'Solution antiseptique', 'Brosse chirurgicale',
        ];
        $types = ['Instrument', 'Consommable', 'Équipement', 'Boîte opératoire', 'Implant', 'Plateau'];
        $adresses = ['Armoire A', 'Armoire B', 'Armoire C', 'Réserve stérile', 'Salle technique', 'Zone anesthésie'];

        return array_map(
            static fn (string $nom, int $index): array => [$nom, sprintf('%s-%02d', $adresses[$index % count($adresses)], intdiv($index, count($adresses)) + 1), $types[$index % count($types)]],
            $noms,
            array_keys($noms),
        );
    }

    /** @param list<Materiel> $materiels
     *
     * @return list<Materiel>
     */
    private function pickMateriels(array $materiels, int $chirurgienIndex, int $modeleIndex): array
    {
        $selection = [];
        $start = (($modeleIndex * 5) + ($chirurgienIndex * 3)) % count($materiels);
        $count = 10 + (($modeleIndex + $chirurgienIndex) % 6);
        for ($offset = 0; $offset < $count; ++$offset) {
            $selection[] = $materiels[($start + ($offset * 7)) % count($materiels)];
        }

        return $selection;
    }
}
