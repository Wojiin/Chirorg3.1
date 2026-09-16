<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add refresh-token families and lookup indexes required by bundle v3.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE refresh_token ADD family VARCHAR(32) DEFAULT NULL, ADD family_valid DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_REFRESH_TOKEN_USERNAME ON refresh_token (username)');
        $this->addSql('CREATE INDEX IDX_REFRESH_TOKEN_VALID ON refresh_token (valid)');
        $this->addSql('CREATE INDEX IDX_REFRESH_TOKEN_FAMILY ON refresh_token (family)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_REFRESH_TOKEN_USERNAME ON refresh_token');
        $this->addSql('DROP INDEX IDX_REFRESH_TOKEN_VALID ON refresh_token');
        $this->addSql('DROP INDEX IDX_REFRESH_TOKEN_FAMILY ON refresh_token');
        $this->addSql('ALTER TABLE refresh_token DROP family, DROP family_valid');
    }
}
