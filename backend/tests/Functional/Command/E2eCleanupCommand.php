<?php

namespace App\Tests\Functional\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:e2e:cleanup', description: 'Supprime uniquement les données temporaires identifiables créées par les scénarios E2E.')]
final class E2eCleanupCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var array<string, int> $deleted */
        $deleted = $this->connection->transactional(function (Connection $connection): array {
            $surgeon = 'Parcours-%';
            $model = 'Intervention parcours %';

            return [
                'chirurgies planifiées' => $connection->executeStatement(
                    <<<'SQL'
                        DELETE cp FROM chirurgie_planifiee cp
                        INNER JOIN chirurgien c ON c.id = cp.chirurgien_id
                        INNER JOIN chirurgie_modele cm ON cm.id = cp.chirurgie_modele_id
                        WHERE c.nom LIKE :surgeon
                           OR cm.intitule LIKE :model
                           OR cp.cree_par LIKE :e2eActor
                           OR cp.cree_par = :fixtureUser
                        SQL,
                    ['surgeon' => $surgeon, 'model' => $model, 'e2eActor' => '%.e2e@chirorg.test', 'fixtureUser' => 'user@chirorg.test'],
                ),
                'listes de matériel' => $connection->executeStatement(
                    <<<'SQL'
                        DELETE lm FROM liste_materiel lm
                        INNER JOIN chirurgien c ON c.id = lm.chirurgien_id
                        INNER JOIN chirurgie_modele cm ON cm.id = lm.chirurgie_modele_id
                        WHERE lm.intitule LIKE :list OR c.nom LIKE :surgeon OR cm.intitule LIKE :model
                        SQL,
                    ['list' => 'Liste parcours %', 'surgeon' => $surgeon, 'model' => $model],
                ),
                'matériels' => $connection->executeStatement(
                    'DELETE FROM materiel WHERE intitule LIKE :materialOne OR intitule LIKE :materialTwo',
                    ['materialOne' => 'Boîte parcours %', 'materialTwo' => 'Implant parcours %'],
                ),
                'chirurgies modèles' => $connection->executeStatement('DELETE FROM chirurgie_modele WHERE intitule LIKE :model', ['model' => $model]),
                'chirurgiens' => $connection->executeStatement('DELETE FROM chirurgien WHERE nom LIKE :surgeon', ['surgeon' => $surgeon]),
                'spécialités' => $connection->executeStatement(
                    'DELETE FROM specialite WHERE intitule LIKE :speciality OR intitule LIKE :simpleSpeciality',
                    ['speciality' => 'Spécialité parcours %', 'simpleSpeciality' => 'Spécialité E2E %'],
                ),
                'utilisateurs' => $connection->executeStatement('DELETE FROM utilisateur WHERE email LIKE :user', ['user' => 'managed.%@chirorg.test']),
            ];
        });

        foreach ($deleted as $resource => $count) {
            $io->writeln(sprintf('%s : %d', ucfirst($resource), $count));
        }
        $io->success(sprintf('%d donnée(s) temporaire(s) E2E supprimée(s).', array_sum($deleted)));

        return Command::SUCCESS;
    }
}
