<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260212224959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE equipment_brand ADD CONSTRAINT FK_F50BD8BCB337437C FOREIGN KEY (equipment_type_id) REFERENCES equipment_type (id)');
        $this->addSql('ALTER TABLE equipment_model ADD CONSTRAINT FK_3ECC533D7436542E FOREIGN KEY (equipment_brand_id) REFERENCES equipment_brand (id)');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
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
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE equipment_brand DROP FOREIGN KEY FK_F50BD8BCB337437C');
        $this->addSql('ALTER TABLE equipment_model DROP FOREIGN KEY FK_3ECC533D7436542E');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195A76ED395');
        $this->addSql('ALTER TABLE repair_order DROP FOREIGN KEY FK_55F65734B03A8386');
        $this->addSql('ALTER TABLE repair_order DROP FOREIGN KEY FK_55F657342F97E6E2');
        $this->addSql('ALTER TABLE repair_order DROP FOREIGN KEY FK_55F6573449B633C1');
        $this->addSql('ALTER TABLE repair_order DROP FOREIGN KEY FK_55F657345E7AA58C');
        $this->addSql('ALTER TABLE repair_order DROP FOREIGN KEY FK_55F65734F4BD7827');
        $this->addSql('ALTER TABLE repair_order_log DROP FOREIGN KEY FK_8D09C5BCE4071493');
        $this->addSql('ALTER TABLE repair_order_log DROP FOREIGN KEY FK_8D09C5BC828AD0A0');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3E4071493');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA31BDD81B');
    }
}
