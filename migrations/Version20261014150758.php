<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261014150758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE archives (id INT AUTO_INCREMENT NOT NULL, entity VARCHAR(100) NOT NULL, entity_id INT NOT NULL, action VARCHAR(50) NOT NULL, data JSON DEFAULT NULL, created_at DATETIME NOT NULL, created_by INT DEFAULT NULL, INDEX idx_archives_entity (entity), INDEX idx_archives_entity_id (entity_id), INDEX idx_archives_created_by (created_by), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE asset_images (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, caption VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, material_id INT NOT NULL, INDEX idx_asset_images_material_id (material_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE deliveries (id INT AUTO_INCREMENT NOT NULL, delivery_date DATETIME NOT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, purchase_order_id INT DEFAULT NULL, received_by INT DEFAULT NULL, INDEX idx_deliveries_purchase_order_id (purchase_order_id), INDEX idx_deliveries_received_by (received_by), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE inventory_movements (id INT AUTO_INCREMENT NOT NULL, movement_type VARCHAR(20) NOT NULL, quantity INT NOT NULL, from_location VARCHAR(255) DEFAULT NULL, to_location VARCHAR(255) DEFAULT NULL, performed_at DATETIME NOT NULL, reason LONGTEXT DEFAULT NULL, material_id INT NOT NULL, performed_by INT DEFAULT NULL, INDEX idx_inventory_movements_material_id (material_id), INDEX idx_inventory_movements_performed_by (performed_by), INDEX idx_inventory_movements_type (movement_type), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE materials (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, reference VARCHAR(100) NOT NULL, serial_number VARCHAR(200) DEFAULT NULL, quantity INT DEFAULT 0 NOT NULL, status VARCHAR(20) DEFAULT \'available\' NOT NULL, location VARCHAR(255) DEFAULT NULL, purchase_date DATETIME DEFAULT NULL, purchase_price NUMERIC(12, 2) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, category_id INT DEFAULT NULL, supplier_id INT DEFAULT NULL, INDEX IDX_9B1716B52ADD6D8C (supplier_id), INDEX idx_materials_category_id (category_id), INDEX idx_materials_status (status), INDEX idx_materials_location (location), UNIQUE INDEX reference (reference), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE purchase_items (id INT AUTO_INCREMENT NOT NULL, material_name VARCHAR(255) NOT NULL, material_reference VARCHAR(100) DEFAULT NULL, quantity INT NOT NULL, unit_price NUMERIC(12, 2) DEFAULT NULL, received_quantity INT DEFAULT 0 NOT NULL, purchase_order_id INT NOT NULL, INDEX idx_purchase_items_purchase_order_id (purchase_order_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE purchase_orders (id INT AUTO_INCREMENT NOT NULL, order_number VARCHAR(100) NOT NULL, status VARCHAR(20) DEFAULT \'pending\' NOT NULL, order_date DATETIME NOT NULL, expected_date DATETIME DEFAULT NULL, total_amount NUMERIC(12, 2) DEFAULT NULL, created_at DATETIME NOT NULL, supplier_id INT DEFAULT NULL, ordered_by INT DEFAULT NULL, INDEX idx_purchase_orders_supplier_id (supplier_id), INDEX idx_purchase_orders_ordered_by (ordered_by), INDEX idx_purchase_orders_status (status), UNIQUE INDEX order_number (order_number), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE suppliers (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, contact_name VARCHAR(255) DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, address LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, full_name VARCHAR(255) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX username (username), UNIQUE INDEX email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE archives ADD CONSTRAINT FK_E262EC39DE12AB56 FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE asset_images ADD CONSTRAINT FK_8323177E308AC6F FOREIGN KEY (material_id) REFERENCES materials (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE deliveries ADD CONSTRAINT FK_6F078568A45D7E6A FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE deliveries ADD CONSTRAINT FK_6F07856838D350E7 FOREIGN KEY (received_by) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE inventory_movements ADD CONSTRAINT FK_BF9F9C49E308AC6F FOREIGN KEY (material_id) REFERENCES materials (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inventory_movements ADD CONSTRAINT FK_BF9F9C4999EB8EA2 FOREIGN KEY (performed_by) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE materials ADD CONSTRAINT FK_9B1716B512469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE materials ADD CONSTRAINT FK_9B1716B52ADD6D8C FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE purchase_items ADD CONSTRAINT FK_35D56A67A45D7E6A FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE purchase_orders ADD CONSTRAINT FK_3E40FFBB2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE purchase_orders ADD CONSTRAINT FK_3E40FFBB2A928C48 FOREIGN KEY (ordered_by) REFERENCES users (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE archives DROP FOREIGN KEY FK_E262EC39DE12AB56');
        $this->addSql('ALTER TABLE asset_images DROP FOREIGN KEY FK_8323177E308AC6F');
        $this->addSql('ALTER TABLE deliveries DROP FOREIGN KEY FK_6F078568A45D7E6A');
        $this->addSql('ALTER TABLE deliveries DROP FOREIGN KEY FK_6F07856838D350E7');
        $this->addSql('ALTER TABLE inventory_movements DROP FOREIGN KEY FK_BF9F9C49E308AC6F');
        $this->addSql('ALTER TABLE inventory_movements DROP FOREIGN KEY FK_BF9F9C4999EB8EA2');
        $this->addSql('ALTER TABLE materials DROP FOREIGN KEY FK_9B1716B512469DE2');
        $this->addSql('ALTER TABLE materials DROP FOREIGN KEY FK_9B1716B52ADD6D8C');
        $this->addSql('ALTER TABLE purchase_items DROP FOREIGN KEY FK_35D56A67A45D7E6A');
        $this->addSql('ALTER TABLE purchase_orders DROP FOREIGN KEY FK_3E40FFBB2ADD6D8C');
        $this->addSql('ALTER TABLE purchase_orders DROP FOREIGN KEY FK_3E40FFBB2A928C48');
        $this->addSql('DROP TABLE archives');
        $this->addSql('DROP TABLE asset_images');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE deliveries');
        $this->addSql('DROP TABLE inventory_movements');
        $this->addSql('DROP TABLE materials');
        $this->addSql('DROP TABLE purchase_items');
        $this->addSql('DROP TABLE purchase_orders');
        $this->addSql('DROP TABLE suppliers');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
