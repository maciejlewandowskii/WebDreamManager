<?php

declare(strict_types=1);

namespace App\Domain\IssueTracker\Application\Data;

use App\Domain\IssueTracker\Enum\IssueStatus;
use App\Domain\IssueTracker\Enum\TrackerType;

final readonly class IssueData
{
    /**
     * @param string[] $labels
     */
    public function __construct(
        public string      $externalId,
        public ?int        $number,
        public string      $title,
        public IssueStatus $status,
        public ?string     $url,
        public ?string     $assignee,
        public array       $labels,
        public TrackerType $trackerType,
    ) {}
}
