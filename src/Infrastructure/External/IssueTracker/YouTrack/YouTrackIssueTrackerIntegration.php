<?php

declare(strict_types=1);

namespace App\Infrastructure\External\IssueTracker\YouTrack;

use App\Domain\Integration\Port\IntegrationInterface;
use App\Domain\Integration\ValueObject\IntegrationField;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.integration', attributes: ['priority' => 16])]
final class YouTrackIssueTrackerIntegration implements IntegrationInterface
{
    public function getKey(): string { return 'youtrack'; }

    public function getName(): string { return 'YouTrack'; }

    public function getDescription(): string
    {
        return 'Connect YouTrack projects and synchronize issues.';
    }

    public function getIcon(): string { return 'simple-icons:youtrack'; }

    public function getFields(): array
    {
        return [
            'YOUTRACK_BASE_URL' => new IntegrationField('YouTrack Base URL', secret: false),
            'YOUTRACK_TOKEN'    => new IntegrationField('Permanent Token', secret: true),
        ];
    }
}
