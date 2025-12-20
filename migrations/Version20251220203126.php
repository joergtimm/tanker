<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251220203126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE opening_time (id INT AUTO_INCREMENT NOT NULL, text VARCHAR(255) NOT NULL, start VARCHAR(20) NOT NULL, end VARCHAR(20) NOT NULL, station_detail_id INT NOT NULL, INDEX IDX_89115E6E5A546DA0 (station_detail_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE opening_time ADD CONSTRAINT FK_89115E6E5A546DA0 FOREIGN KEY (station_detail_id) REFERENCES station_detail (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE opening_time DROP FOREIGN KEY FK_89115E6E5A546DA0');
        $this->addSql('DROP TABLE opening_time');
    }
}
