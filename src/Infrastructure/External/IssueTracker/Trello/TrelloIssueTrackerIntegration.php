<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker\Trello;

use App\Domain\Integration\Port\IntegrationInterface;
use App\Domain\Integration\ValueObject\IntegrationField;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration', attributes: ['priority' => 18])]
final class TrelloIssueTrackerIntegration implements IntegrationInterface
{
    public function getKey(): string { return 'trello'; }

    public function getName(): string { return 'Trello'; }

    public function getDescription(): string
    {
        return 'Link Trello boards to projects and view cards as issues.';
    }

    public function getIcon(): string { return 'logos:trello'; }

    public function getFields(): array
    {
        return [
            'TRELLO_API_KEY' => new IntegrationField('API Key', secret: false),
            'TRELLO_TOKEN'   => new IntegrationField('Token', secret: true),
        ];
    }
}
