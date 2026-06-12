<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260610173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create EntityAudit tables for Invoice and Quote';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE quotes_audit (id UUID NOT NULL, number VARCHAR(50) DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, issued_at DATE DEFAULT NULL, valid_until DATE DEFAULT NULL, currency VARCHAR(3) DEFAULT 'PLN', default_tax_rate NUMERIC(5, 2) DEFAULT '23', notes TEXT DEFAULT NULL, intro_text TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, customer_id UUID DEFAULT NULL, project_id UUID DEFAULT NULL, rev INT NOT NULL, revtype VARCHAR(4) NOT NULL, PRIMARY KEY (id, rev))");
        $this->addSql('CREATE INDEX rev_115c5dd356a7b551cd673e90ff2d025f_idx ON quotes_audit (rev)');
        $this->addSql("CREATE TABLE invoices_audit (id UUID NOT NULL, number VARCHAR(50) DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, issued_at DATE DEFAULT NULL, due_at DATE DEFAULT NULL, currency VARCHAR(3) DEFAULT 'PLN', default_tax_rate NUMERIC(5, 2) DEFAULT '23', notes TEXT DEFAULT NULL, payment_terms TEXT DEFAULT NULL, bank_account VARCHAR(200) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, customer_id UUID DEFAULT NULL, project_id UUID DEFAULT NULL, rev INT NOT NULL, revtype VARCHAR(4) NOT NULL, PRIMARY KEY (id, rev))");
        $this->addSql('CREATE INDEX rev_33d070587a67221ed9650b4e6a15d80a_idx ON invoices_audit (rev)');
        $this->addSql('ALTER TABLE quotes_audit ADD CONSTRAINT rev_115c5dd356a7b551cd673e90ff2d025f_fk FOREIGN KEY (rev) REFERENCES revisions (id)');
        $this->addSql('ALTER TABLE invoices_audit ADD CONSTRAINT rev_33d070587a67221ed9650b4e6a15d80a_fk FOREIGN KEY (rev) REFERENCES revisions (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quotes_audit DROP CONSTRAINT rev_115c5dd356a7b551cd673e90ff2d025f_fk');
        $this->addSql('ALTER TABLE invoices_audit DROP CONSTRAINT rev_33d070587a67221ed9650b4e6a15d80a_fk');
        $this->addSql('DROP TABLE quotes_audit');
        $this->addSql('DROP TABLE invoices_audit');
    }
}
