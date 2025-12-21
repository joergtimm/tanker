<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221123641 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_station (user_id INT NOT NULL, station_id INT NOT NULL, INDEX IDX_C734E6BBA76ED395 (user_id), INDEX IDX_C734E6BB21BDB235 (station_id), PRIMARY KEY (user_id, station_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_station ADD CONSTRAINT FK_C734E6BBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_station ADD CONSTRAINT FK_C734E6BB21BDB235 FOREIGN KEY (station_id) REFERENCES station (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_station DROP FOREIGN KEY FK_C734E6BBA76ED395');
        $this->addSql('ALTER TABLE user_station DROP FOREIGN KEY FK_C734E6BB21BDB235');
        $this->addSql('DROP TABLE user_station');
    }
}
