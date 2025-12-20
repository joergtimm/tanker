<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251220202508 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE station_detail (id INT AUTO_INCREMENT NOT NULL, opening_times JSON DEFAULT NULL, overrides JSON DEFAULT NULL, whole_day TINYINT DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, station_id INT NOT NULL, UNIQUE INDEX UNIQ_E1E46C7521BDB235 (station_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE station_detail ADD CONSTRAINT FK_E1E46C7521BDB235 FOREIGN KEY (station_id) REFERENCES station (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE station_detail DROP FOREIGN KEY FK_E1E46C7521BDB235');
        $this->addSql('DROP TABLE station_detail');
    }
}
