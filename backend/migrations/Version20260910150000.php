<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260910150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Garantit la présence de la spécialité système « Sans spécialité ».';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO specialite (intitule) SELECT 'Sans spécialité' WHERE NOT EXISTS (SELECT 1 FROM specialite WHERE intitule = 'Sans spécialité')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM specialite WHERE intitule = 'Sans spécialité' AND NOT EXISTS (SELECT 1 FROM chirurgien WHERE chirurgien.specialite_id = specialite.id) AND NOT EXISTS (SELECT 1 FROM materiel WHERE materiel.specialite_id = specialite.id) AND NOT EXISTS (SELECT 1 FROM chirurgie_modele WHERE chirurgie_modele.specialite_id = specialite.id)");
    }
}
