<?php

declare(strict_types=1);

namespace App\Domain\IssueTracker\Enum;

enum TrackerType: string
{
    case None     = 'none';
    case GitHub   = 'github';
    case Jira     = 'jira';
    case Trello   = 'trello';
    case ClickUp  = 'clickup';
    case YouTrack = 'youtrack';

    public function label(): string
    {
        return match ($this) {
            self::None     => '— None —',
            self::GitHub   => 'GitHub Issues',
            self::Jira     => 'Jira',
            self::Trello   => 'Trello',
            self::ClickUp  => 'ClickUp',
            self::YouTrack => 'YouTrack',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::None     => 'tabler:circle-off',
            self::GitHub   => 'logos:github-icon',
            self::Jira     => 'logos:jira',
            self::Trello   => 'logos:trello',
            self::ClickUp  => 'simple-icons:clickup',
            self::YouTrack => 'simple-icons:youtrack',
        };
    }

    public function resourcePlaceholder(): string
    {
        return match ($this) {
            self::None     => '',
            self::GitHub   => 'owner/repo',
            self::Jira     => 'PROJECT-KEY',
            self::Trello   => 'board-id',
            self::ClickUp  => 'list-id',
            self::YouTrack => 'project-short-name',
        };
    }

    public function integrationKey(): string
    {
        return match ($this) {
            self::None     => '',
            self::GitHub   => 'github',
            self::Jira     => 'jira',
            self::Trello   => 'trello',
            self::ClickUp  => 'clickup',
            self::YouTrack => 'youtrack',
        };
    }
}
