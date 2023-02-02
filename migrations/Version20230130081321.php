<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230130081321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE removal_container');
        $this->addSql('ALTER TABLE provider CHANGE zip_code zip_code VARCHAR(10) DEFAULT NULL, CHANGE commercial_contact_name commercial_contact_name VARCHAR(255) DEFAULT NULL, CHANGE commercial_contact_phone commercial_contact_phone VARCHAR(20) DEFAULT NULL, CHANGE commercial_contact_mail commercial_contact_mail VARCHAR(255) DEFAULT NULL, CHANGE removal_contact_name removal_contact_name VARCHAR(255) DEFAULT NULL, CHANGE removal_contact_phone removal_contact_phone VARCHAR(20) DEFAULT NULL, CHANGE removal_contact_mail removal_contact_mail VARCHAR(255) DEFAULT NULL, CHANGE certificate_contact_mail certificate_contact_mail VARCHAR(255) DEFAULT NULL, CHANGE city city VARCHAR(100) DEFAULT NULL, CHANGE removal_contact_name_two removal_contact_name_two VARCHAR(255) DEFAULT NULL, CHANGE removal_contact_phone_two removal_contact_phone_two VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE recycler CHANGE commercial_contact_name commercial_contact_name VARCHAR(255) DEFAULT NULL, CHANGE commercial_contact_mail commercial_contact_mail VARCHAR(255) DEFAULT NULL, CHANGE commercial_contact_phone commercial_contact_phone VARCHAR(255) DEFAULT NULL, CHANGE contact_name contact_name VARCHAR(255) DEFAULT NULL, CHANGE contact_tel_one contact_tel_one VARCHAR(20) DEFAULT NULL, CHANGE contact_tel_two contact_tel_two VARCHAR(20) DEFAULT NULL, CHANGE contact_mail contact_mail VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE vehicle ADD hgv TINYINT(1) DEFAULT NULL');
        $this->addSql('UPDATE vehicle SET hgv = true WHERE vehicle.id = 4 OR vehicle.id = 5');
        $this->addSql('UPDATE vehicle SET hgv = false WHERE vehicle.id NOT IN (4,5)');
        $this->addSql('UPDATE vehicle SET name = "Renault 169" WHERE vehicle.id = 4');
        $this->addSql('UPDATE vehicle SET name = "Renault 602" WHERE vehicle.id = 5');
        $this->addSql('ALTER TABLE vehicle MODIFY COLUMN hgv TINYINT(1) NOT NULL');
        //$this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE removal_container (removal_id INT NOT NULL, container_id INT NOT NULL, INDEX IDX_C49A5119BC21F742 (container_id), INDEX IDX_C49A5119A00B94E6 (removal_id), PRIMARY KEY(removal_id, container_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE removal_container ADD CONSTRAINT FK_C49A5119BC21F742 FOREIGN KEY (container_id) REFERENCES container (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE removal_container ADD CONSTRAINT FK_C49A5119A00B94E6 FOREIGN KEY (removal_id) REFERENCES removal (id) ON DELETE CASCADE');
        //$this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE provider CHANGE zip_code zip_code VARCHAR(10) DEFAULT \'NULL\', CHANGE city city VARCHAR(100) DEFAULT \'NULL\', CHANGE commercial_contact_name commercial_contact_name VARCHAR(255) DEFAULT \'NULL\', CHANGE commercial_contact_phone commercial_contact_phone VARCHAR(20) DEFAULT \'NULL\', CHANGE commercial_contact_mail commercial_contact_mail VARCHAR(255) DEFAULT \'NULL\', CHANGE removal_contact_name removal_contact_name VARCHAR(255) DEFAULT \'NULL\', CHANGE removal_contact_phone removal_contact_phone VARCHAR(20) DEFAULT \'NULL\', CHANGE removal_contact_name_two removal_contact_name_two VARCHAR(255) DEFAULT \'NULL\', CHANGE removal_contact_phone_two removal_contact_phone_two VARCHAR(255) DEFAULT \'NULL\', CHANGE removal_contact_mail removal_contact_mail VARCHAR(255) DEFAULT \'NULL\', CHANGE certificate_contact_mail certificate_contact_mail VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE recycler CHANGE commercial_contact_name commercial_contact_name VARCHAR(255) DEFAULT \'NULL\', CHANGE commercial_contact_mail commercial_contact_mail VARCHAR(255) DEFAULT \'NULL\', CHANGE commercial_contact_phone commercial_contact_phone VARCHAR(255) DEFAULT \'NULL\', CHANGE contact_name contact_name VARCHAR(255) DEFAULT \'NULL\', CHANGE contact_tel_one contact_tel_one VARCHAR(20) DEFAULT \'NULL\', CHANGE contact_tel_two contact_tel_two VARCHAR(20) DEFAULT \'NULL\', CHANGE contact_mail contact_mail VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE vehicle DROP hgv');
        $this->addSql('UPDATE vehicle SET name = "PL Renault 169" WHERE vehicle.id = 4');
        $this->addSql('UPDATE vehicle SET name = "PL Renault 602" WHERE vehicle.id = 5');
    }
}
