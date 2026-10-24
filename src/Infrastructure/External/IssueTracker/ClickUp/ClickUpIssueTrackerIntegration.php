<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker\ClickUp;

use App\Domain\Integration\Port\IntegrationInterface;
use App\Domain\Integration\ValueObject\IntegrationField;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration', attributes: ['priority' => 17])]
final class ClickUpIssueTrackerIntegration implements IntegrationInterface
{
    public function getKey(): string { return 'clickup'; }

    public function getName(): string { return 'ClickUp'; }

    public function getDescription(): string
    {
        return 'Sync ClickUp tasks and lists as project issues.';
    }

    public function getIcon(): string { return 'simple-icons:clickup'; }

    public function getFields(): array
    {
        return [
            'CLICKUP_API_TOKEN' => new IntegrationField('API Token', secret: true),
            'CLICKUP_TEAM_ID'   => new IntegrationField('Team ID', secret: false),
        ];
    }
}
