<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker\GitHub;

use App\Domain\Integration\Port\IntegrationInterface;
use App\Domain\Integration\ValueObject\IntegrationField;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration', attributes: ['priority' => 20])]
final class GitHubIssueTrackerIntegration implements IntegrationInterface
{
    public function getKey(): string { return 'github'; }

    public function getName(): string { return 'GitHub Issues'; }

    public function getDescription(): string
    {
        return 'Link projects to GitHub repositories and sync issues directly.';
    }

    public function getIcon(): string { return 'logos:github-icon'; }

    public function getFields(): array
    {
        return [
            'GITHUB_TOKEN'   => new IntegrationField('Personal Access Token', secret: true),
            'GITHUB_API_URL' => new IntegrationField('API Base URL (optional override)', secret: false),
        ];
    }
}
