<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker\Jira;

use App\Domain\Integration\Port\IntegrationInterface;
use App\Domain\Integration\ValueObject\IntegrationField;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration', attributes: ['priority' => 19])]
final class JiraIssueTrackerIntegration implements IntegrationInterface
{
    public function getKey(): string { return 'jira'; }

    public function getName(): string { return 'Jira'; }

    public function getDescription(): string
    {
        return 'Connect to Jira Cloud projects and sync issues.';
    }

    public function getIcon(): string { return 'logos:jira'; }

    public function getFields(): array
    {
        return [
            'JIRA_BASE_URL'  => new IntegrationField('Jira Base URL', secret: false),
            'JIRA_EMAIL'     => new IntegrationField('Account Email', secret: false),
            'JIRA_API_TOKEN' => new IntegrationField('API Token', secret: true),
        ];
    }
}
