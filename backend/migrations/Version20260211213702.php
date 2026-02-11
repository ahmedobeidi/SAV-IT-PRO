<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211213702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, phone VARCHAR(30) NOT NULL, email VARCHAR(180) DEFAULT NULL, address LONGTEXT DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, landline_phone VARCHAR(30) DEFAULT NULL, is_anonymized TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE equipment_brand (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, equipment_type_id INT NOT NULL, INDEX IDX_F50BD8BCB337437C (equipment_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE equipment_model (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, equipment_brand_id INT NOT NULL, INDEX IDX_3ECC533D7436542E (equipment_brand_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE equipment_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE issue (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE repair_order (id INT AUTO_INCREMENT NOT NULL, price DOUBLE PRECISION NOT NULL, deposit DOUBLE PRECISION DEFAULT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, created_by_id INT NOT NULL, created_for_id INT NOT NULL, equipment_model_id INT NOT NULL, issue_id INT NOT NULL, assigned_to_id INT DEFAULT NULL, INDEX IDX_55F65734B03A8386 (created_by_id), INDEX IDX_55F657342F97E6E2 (created_for_id), INDEX IDX_55F6573449B633C1 (equipment_model_id), INDEX IDX_55F657345E7AA58C (issue_id), INDEX IDX_55F65734F4BD7827 (assigned_to_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE repair_order_log (id INT AUTO_INCREMENT NOT NULL, changed_at DATETIME NOT NULL, snapshot JSON NOT NULL, action VARCHAR(255) NOT NULL, repair_order_id INT NOT NULL, changed_by_id INT NOT NULL, INDEX IDX_8D09C5BCE4071493 (repair_order_id), INDEX IDX_8D09C5BC828AD0A0 (changed_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, content LONGBLOB NOT NULL, filename VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, size INT NOT NULL, generated_at DATETIME NOT NULL, is_sent TINYINT DEFAULT 0 NOT NULL, repair_order_id INT NOT NULL, generated_by_id INT NOT NULL, INDEX IDX_97A0ADA3E4071493 (repair_order_id), INDEX IDX_97A0ADA31BDD81B (generated_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, is_active TINYINT DEFAULT 1 NOT NULL, is_anonymized TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE equipment_brand ADD CONSTRAINT FK_F50BD8BCB337437C FOREIGN KEY (equipment_type_id) REFERENCES equipment_type (id)');
        $this->addSql('ALTER TABLE equipment_model ADD CONSTRAINT FK_3ECC533D7436542E FOREIGN KEY (equipment_brand_id) REFERENCES equipment_brand (id)');
        $this->addSql('ALTER TABLE repair_order ADD CONSTRAINT FK_55F65734B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE repair_order ADD CONSTRAINT FK_55F657342F97E6E2 FOREIGN KEY (created_for_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE repair_order ADD CONSTRAINT FK_55F6573449B633C1 FOREIGN KEY (equipment_model_id) REFERENCES equipment_model (id)');
        $this->addSql('ALTER TABLE repair_order ADD CONSTRAINT FK_55F657345E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id)');
        $this->addSql('ALTER TABLE repair_order ADD CONSTRAINT FK_55F65734F4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE repair_order_log ADD CONSTRAINT FK_8D09C5BCE4071493 FOREIGN KEY (repair_order_id) REFERENCES repair_order (id)');
        $this->addSql('ALTER TABLE repair_order_log ADD CONSTRAINT FK_8D09C5BC828AD0A0 FOREIGN KEY (changed_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3E4071493 FOREIGN KEY (repair_order_id) REFERENCES repair_order (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA31BDD81B FOREIGN KEY (generated_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment_brand DROP FOREIGN KEY FK_F50BD8BCB337437C');
        $this->addSql('ALTER TABLE equipment_model DROP FOREIGN KEY FK_3ECC533D7436542E');
        $this->addSql('ALTER TABLE repair_order DROP FOREIGN KEY FK_55F65734B03A8386');
        $this->addSql('ALTER TABLE repair_order DROP FOREIGN KEY FK_55F657342F97E6E2');
        $this->addSql('ALTER TABLE repair_order DROP FOREIGN KEY FK_55F6573449B633C1');
        $this->addSql('ALTER TABLE repair_order DROP FOREIGN KEY FK_55F657345E7AA58C');
        $this->addSql('ALTER TABLE repair_order DROP FOREIGN KEY FK_55F65734F4BD7827');
        $this->addSql('ALTER TABLE repair_order_log DROP FOREIGN KEY FK_8D09C5BCE4071493');
        $this->addSql('ALTER TABLE repair_order_log DROP FOREIGN KEY FK_8D09C5BC828AD0A0');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3E4071493');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA31BDD81B');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE equipment_brand');
        $this->addSql('DROP TABLE equipment_model');
        $this->addSql('DROP TABLE equipment_type');
        $this->addSql('DROP TABLE issue');
        $this->addSql('DROP TABLE repair_order');
        $this->addSql('DROP TABLE repair_order_log');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE user');
    }
}
