<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260908160141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Garantit un ordre unique dans chaque programme opératoire.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX uniq_chirurgie_programme_ordre ON chirurgie_planifiee (date_programmee, salle, chirurgien_id, ordre)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_chirurgie_programme_ordre ON chirurgie_planifiee');
    }
}
