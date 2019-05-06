<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190506153038 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE client DROP password');
        $this->addSql('ALTER TABLE mobile ADD client_id INT NOT NULL');
        $this->addSql('ALTER TABLE mobile ADD CONSTRAINT FK_3C7323E019EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_3C7323E019EB6921 ON mobile (client_id)');
        $this->addSql('ALTER TABLE user ADD password VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD active TINYINT(1) DEFAULT NULL, ADD role VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, DROP updated_at');
    }
}
