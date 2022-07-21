<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220720220356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction ADD user__id INT NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD course_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D18D57A4BB FOREIGN KEY (user__id) REFERENCES "billing_user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1591CC992 FOREIGN KEY (course_id) REFERENCES course (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_723705D18D57A4BB ON transaction (user__id)');
        $this->addSql('CREATE INDEX IDX_723705D1591CC992 ON transaction (course_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D18D57A4BB');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D1591CC992');
        $this->addSql('DROP INDEX IDX_723705D18D57A4BB');
        $this->addSql('DROP INDEX IDX_723705D1591CC992');
        $this->addSql('ALTER TABLE transaction DROP user__id');
        $this->addSql('ALTER TABLE transaction DROP course_id');
    }
}
