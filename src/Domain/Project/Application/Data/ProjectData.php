<?php

declare(strict_types=1);

namespace App\Domain\Project\Application\Data;

use App\Domain\Customer\Entity\Customer;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Entity\ProjectStatus;
use Symfony\Component\Validator\Constraints as Assert;

final class ProjectData
{
    #[Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(min: 2, max: 200)])]
    public string $name = '';

    #[Assert\NotNull(message: 'Please select a customer.')]
    public ?Customer $customer = null;

    public ProjectStatus $status = ProjectStatus::Planning;

    public ?string $description = null;

    #[Assert\Url(requireTld: true)]
    #[Assert\Length(max: 500)]
    public ?string $websiteUrl = null;

    #[Assert\Length(max: 500)]
    public ?string $githubRepository = null;

    #[Assert\PositiveOrZero]
    #[Assert\LessThan(100000000)]
    public ?float $budget = null;

    public static function fromEntity(Project $project): self
    {
        $data = new self();
        $data->name             = $project->getName();
        $data->customer         = $project->getCustomer();
        $data->status           = $project->getStatus();
        $data->description      = $project->getDescription();
        $data->websiteUrl       = $project->getWebsiteUrl();
        $data->githubRepository = $project->getGithubRepository();
        $data->budget           = $project->getBudget() !== null ? (float) $project->getBudget() : null;

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
        $project->setGithubRepository($this->githubRepository !== '' ? $this->githubRepository : null);
        $project->setBudget($this->budget !== null ? (string) $this->budget : null);
    }
}
