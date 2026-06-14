<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Email;

use App\Domain\Integration\Port\IntegrationInterface;
use App\Domain\Integration\ValueObject\IntegrationField;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration', attributes: ['priority' => 30])]
final class EmailIntegration implements IntegrationInterface
{
    public function getKey(): string
    {
        return 'mail';
    }

    public function getName(): string
    {
        return 'Email';
    }

    public function getDescription(): string
    {
        return 'Send transactional emails to customers and users via SMTP.';
    }

    public function getIcon(): string
    {
        return 'tabler:mail';
    }

    public function getFields(): array
    {
        return [
            'MAILER_DSN'       => new IntegrationField('Mailer DSN',    secret: true),
            'MAILER_FROM'      => new IntegrationField('From Address',  secret: false),
            'MAILER_FROM_NAME' => new IntegrationField('From Name',     secret: false),
        ];
    }
}
