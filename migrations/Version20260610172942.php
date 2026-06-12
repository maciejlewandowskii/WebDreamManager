<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610172942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice_pdf_records (id UUID NOT NULL, color_mode VARCHAR(20) NOT NULL, file_path VARCHAR(500) NOT NULL, file_name VARCHAR(200) NOT NULL, generated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, invoice_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_F869BB5A2989F1FD ON invoice_pdf_records (invoice_id)');
        $this->addSql('CREATE TABLE quote_pdf_records (id UUID NOT NULL, color_mode VARCHAR(20) NOT NULL, file_path VARCHAR(500) NOT NULL, file_name VARCHAR(200) NOT NULL, generated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, quote_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B324E417DB805178 ON quote_pdf_records (quote_id)');
        $this->addSql('ALTER TABLE invoice_pdf_records ADD CONSTRAINT FK_F869BB5A2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE quote_pdf_records ADD CONSTRAINT FK_B324E417DB805178 FOREIGN KEY (quote_id) REFERENCES quotes (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice_pdf_records DROP CONSTRAINT FK_F869BB5A2989F1FD');
        $this->addSql('ALTER TABLE quote_pdf_records DROP CONSTRAINT FK_B324E417DB805178');
        $this->addSql('DROP TABLE invoice_pdf_records');
        $this->addSql('DROP TABLE quote_pdf_records');
    }
}
