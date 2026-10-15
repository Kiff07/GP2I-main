<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Lot 2 : table des emprunts (loans) + seuil d'alerte propre a chaque materiel.
 */
final class Version20261015120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Emprunts de materiel et seuil d alerte stock personnalise';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE loans (id INT AUTO_INCREMENT NOT NULL, borrower_name VARCHAR(255) NOT NULL, borrowed_at DATETIME NOT NULL, due_date DATETIME NOT NULL, returned_at DATETIME DEFAULT NULL, note LONGTEXT DEFAULT NULL, material_id INT NOT NULL, performed_by INT DEFAULT NULL, INDEX idx_loans_material_id (material_id), INDEX idx_loans_returned_at (returned_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE loans ADD CONSTRAINT FK_loans_material FOREIGN KEY (material_id) REFERENCES materials (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE loans ADD CONSTRAINT FK_loans_performed_by FOREIGN KEY (performed_by) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE materials ADD alert_threshold INT DEFAULT 2 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE loans DROP FOREIGN KEY FK_loans_material');
        $this->addSql('ALTER TABLE loans DROP FOREIGN KEY FK_loans_performed_by');
        $this->addSql('DROP TABLE loans');
        $this->addSql('ALTER TABLE materials DROP alert_threshold');
    }
}
