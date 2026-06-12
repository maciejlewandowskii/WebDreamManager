<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260611200616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoices ADD stripe_session_id VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoices ADD payment_token VARCHAR(100) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2F2F9587E9789 ON invoices (payment_token)');
        $this->addSql('ALTER TABLE invoices_audit ADD stripe_session_id VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoices_audit ADD payment_token VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_6A2F2F9587E9789');
        $this->addSql('ALTER TABLE invoices DROP stripe_session_id');
        $this->addSql('ALTER TABLE invoices DROP payment_token');
        $this->addSql('ALTER TABLE invoices_audit DROP stripe_session_id');
        $this->addSql('ALTER TABLE invoices_audit DROP payment_token');
    }
}
