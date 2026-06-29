<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Data;

use App\Domain\Customer\Entity\Customer;
use App\Domain\IssueTracker\Enum\TrackerType;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Entity\ProjectStatus;

final class ProjectData
{
    public string $name = '';
    public ?Customer $customer = null;
    public ProjectStatus $status = ProjectStatus::Planning;
    public ?string $description = null;
    public ?string $websiteUrl = null;
    public TrackerType $trackerType = TrackerType::None;
    public ?string $trackerResource = null;
    public ?float $budget = null;

    public static function fromEntity(Project $project): self
    {
        $data = new self();
        $data->name            = $project->getName();
        $data->customer        = $project->getCustomer();
        $data->status          = $project->getStatus();
        $data->description     = $project->getDescription();
        $data->websiteUrl      = $project->getWebsiteUrl();
        $data->trackerType     = $project->getTrackerType();
        $data->trackerResource = $project->getTrackerResource();
        $data->budget          = $project->getBudget() !== null ? (float) $project->getBudget() : null;

        return $data;
    }

    public function applyTo(Project $project): void
    {
        $project->setName($this->name);
        if ($this->customer !== null) {
            $project->setCustomer($this->customer);
        }
        $project->setStatus($this->status);
        $project->setDescription($this->description !== '' ? $this->description : null);
        $project->setWebsiteUrl($this->websiteUrl !== '' ? $this->websiteUrl : null);
        $project->setTrackerType($this->trackerType);
        $project->setTrackerResource($this->trackerResource !== '' ? $this->trackerResource : null);
        $project->setBudget($this->budget !== null ? (string) $this->budget : null);
    }
}
