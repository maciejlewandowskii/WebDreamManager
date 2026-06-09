<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260609125636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customers (id UUID NOT NULL, name VARCHAR(200) NOT NULL, email VARCHAR(180) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, company VARCHAR(200) DEFAULT NULL, address VARCHAR(500) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(10) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, status VARCHAR(50) NOT NULL, notes TEXT DEFAULT NULL, tax_id VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE invoice_items (id UUID NOT NULL, description VARCHAR(500) NOT NULL, quantity NUMERIC(10, 2) NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, tax_rate NUMERIC(5, 2) DEFAULT \'0\' NOT NULL, sort_order INT DEFAULT 0 NOT NULL, invoice_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_DCC4B9F82989F1FD ON invoice_items (invoice_id)');
        $this->addSql('CREATE TABLE invoices (id UUID NOT NULL, number VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, issued_at DATE NOT NULL, due_at DATE NOT NULL, currency VARCHAR(3) DEFAULT \'PLN\' NOT NULL, default_tax_rate NUMERIC(5, 2) DEFAULT \'23\' NOT NULL, notes TEXT DEFAULT NULL, payment_terms TEXT DEFAULT NULL, bank_account VARCHAR(200) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, customer_id UUID NOT NULL, project_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2F2F9596901F54 ON invoices (number)');
        $this->addSql('CREATE INDEX IDX_6A2F2F959395C3F3 ON invoices (customer_id)');
        $this->addSql('CREATE INDEX IDX_6A2F2F95166D1F9C ON invoices (project_id)');
        $this->addSql('CREATE TABLE projects (id UUID NOT NULL, name VARCHAR(200) NOT NULL, description TEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, website_url VARCHAR(500) DEFAULT NULL, github_repository VARCHAR(500) DEFAULT NULL, files_path VARCHAR(1000) DEFAULT NULL, budget NUMERIC(10, 2) DEFAULT NULL, currency VARCHAR(3) DEFAULT \'PLN\' NOT NULL, start_date DATE DEFAULT NULL, due_date DATE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, customer_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_5C93B3A49395C3F3 ON projects (customer_id)');
        $this->addSql('CREATE TABLE quote_items (id UUID NOT NULL, description VARCHAR(500) NOT NULL, quantity NUMERIC(10, 2) NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, tax_rate NUMERIC(5, 2) DEFAULT \'0\' NOT NULL, sort_order INT DEFAULT 0 NOT NULL, quote_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_ECE1642CDB805178 ON quote_items (quote_id)');
        $this->addSql('CREATE TABLE quotes (id UUID NOT NULL, number VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, issued_at DATE NOT NULL, valid_until DATE DEFAULT NULL, currency VARCHAR(3) DEFAULT \'PLN\' NOT NULL, default_tax_rate NUMERIC(5, 2) DEFAULT \'23\' NOT NULL, notes TEXT DEFAULT NULL, intro_text TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, customer_id UUID NOT NULL, project_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A1B588C596901F54 ON quotes (number)');
        $this->addSql('CREATE INDEX IDX_A1B588C59395C3F3 ON quotes (customer_id)');
        $this->addSql('CREATE INDEX IDX_A1B588C5166D1F9C ON quotes (project_id)');
        $this->addSql('CREATE TABLE time_records (id UUID NOT NULL, title VARCHAR(300) NOT NULL, description TEXT DEFAULT NULL, estimated_hours NUMERIC(6, 2) DEFAULT NULL, spent_hours NUMERIC(6, 2) NOT NULL, date DATE NOT NULL, github_issue_id INT DEFAULT NULL, invoiced BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, project_id UUID NOT NULL, worker_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_C81AF920166D1F9C ON time_records (project_id)');
        $this->addSql('CREATE INDEX IDX_C81AF9206B20BA36 ON time_records (worker_id)');
        $this->addSql('CREATE TABLE users (id UUID NOT NULL, email VARCHAR(180) NOT NULL, full_name VARCHAR(100) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, avatar_url VARCHAR(255) DEFAULT NULL, email_auth_enabled BOOLEAN NOT NULL, email_auth_code VARCHAR(255) DEFAULT NULL, totp_auth_enabled BOOLEAN NOT NULL, totp_secret VARCHAR(255) DEFAULT NULL, backup_codes JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('ALTER TABLE invoice_items ADD CONSTRAINT FK_DCC4B9F82989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F959395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE projects ADD CONSTRAINT FK_5C93B3A49395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE quote_items ADD CONSTRAINT FK_ECE1642CDB805178 FOREIGN KEY (quote_id) REFERENCES quotes (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE quotes ADD CONSTRAINT FK_A1B588C59395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE quotes ADD CONSTRAINT FK_A1B588C5166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE time_records ADD CONSTRAINT FK_C81AF920166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE time_records ADD CONSTRAINT FK_C81AF9206B20BA36 FOREIGN KEY (worker_id) REFERENCES users (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_items DROP CONSTRAINT FK_DCC4B9F82989F1FD');
        $this->addSql('ALTER TABLE invoices DROP CONSTRAINT FK_6A2F2F959395C3F3');
        $this->addSql('ALTER TABLE invoices DROP CONSTRAINT FK_6A2F2F95166D1F9C');
        $this->addSql('ALTER TABLE projects DROP CONSTRAINT FK_5C93B3A49395C3F3');
        $this->addSql('ALTER TABLE quote_items DROP CONSTRAINT FK_ECE1642CDB805178');
        $this->addSql('ALTER TABLE quotes DROP CONSTRAINT FK_A1B588C59395C3F3');
        $this->addSql('ALTER TABLE quotes DROP CONSTRAINT FK_A1B588C5166D1F9C');
        $this->addSql('ALTER TABLE time_records DROP CONSTRAINT FK_C81AF920166D1F9C');
        $this->addSql('ALTER TABLE time_records DROP CONSTRAINT FK_C81AF9206B20BA36');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE invoice_items');
        $this->addSql('DROP TABLE invoices');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE quote_items');
        $this->addSql('DROP TABLE quotes');
        $this->addSql('DROP TABLE time_records');
        $this->addSql('DROP TABLE users');
    }
}
