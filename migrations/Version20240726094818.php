<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240726094818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, priority INT DEFAULT NULL, UNIQUE INDEX UNIQ_57698A6A5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_group (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, tasks LONGTEXT NOT NULL, UNIQUE INDEX UNIQ_AA645FE5AA9E377A (date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_witness_date (id INT AUTO_INCREMENT NOT NULL, role_id INT NOT NULL, witness_id INT NOT NULL, date DATE NOT NULL, task VARCHAR(255) NOT NULL, INDEX IDX_5AA8F62AD60322AC (role_id), INDEX IDX_5AA8F62AF28D7E1C (witness_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE witness (id INT AUTO_INCREMENT NOT NULL, full_name VARCHAR(64) NOT NULL, UNIQUE INDEX UNIQ_CFF1AA0EDBC463C4 (full_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE witness_role (witness_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_6FCE6F17F28D7E1C (witness_id), INDEX IDX_6FCE6F17D60322AC (role_id), PRIMARY KEY(witness_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task_witness_date ADD CONSTRAINT FK_5AA8F62AD60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE task_witness_date ADD CONSTRAINT FK_5AA8F62AF28D7E1C FOREIGN KEY (witness_id) REFERENCES witness (id)');
        $this->addSql('ALTER TABLE witness_role ADD CONSTRAINT FK_6FCE6F17F28D7E1C FOREIGN KEY (witness_id) REFERENCES witness (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE witness_role ADD CONSTRAINT FK_6FCE6F17D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_witness_date DROP FOREIGN KEY FK_5AA8F62AD60322AC');
        $this->addSql('ALTER TABLE task_witness_date DROP FOREIGN KEY FK_5AA8F62AF28D7E1C');
        $this->addSql('ALTER TABLE witness_role DROP FOREIGN KEY FK_6FCE6F17F28D7E1C');
        $this->addSql('ALTER TABLE witness_role DROP FOREIGN KEY FK_6FCE6F17D60322AC');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE task_group');
        $this->addSql('DROP TABLE task_witness_date');
        $this->addSql('DROP TABLE witness');
        $this->addSql('DROP TABLE witness_role');
    }
}
