<?php

declare(strict_types=1);

namespace App\UI\Component\Project;

use App\Domain\Customer\Repository\CustomerRepositoryInterface;
use App\Domain\Project\Entity\Project;
use App\Domain\Project\Entity\ProjectStatus;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[AsLiveComponent]
final class ProjectForm
{
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    #[LiveProp(writable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 200)]
    public string $name = '';

    #[LiveProp(writable: true)]
    public string $description = '';

    #[LiveProp(writable: true)]
    #[Assert\NotBlank]
    public string $customerId = '';

    #[LiveProp(writable: true)]
    public string $websiteUrl = '';

    #[LiveProp(writable: true)]
    public string $githubRepository = '';

    #[LiveProp(writable: true)]
    public string $status = ProjectStatus::Planning->value;

    #[LiveProp]
    public ?string $projectId = null;

    private ?Project $existingProject = null;

    public function __construct(
        private readonly ProjectRepositoryInterface $projectRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
    ) {
    }

    public function mount(?string $projectId = null): void
    {
        if ($projectId !== null) {
            $this->projectId = $projectId;
            $project = $this->projectRepository->findById($projectId);
            if ($project !== null) {
                $this->existingProject = $project;
                $this->name              = $project->getName();
                $this->description       = $project->getDescription() ?? '';
                $this->customerId        = $project->getCustomer()->getId();
                $this->websiteUrl        = $project->getWebsiteUrl() ?? '';
                $this->githubRepository  = $project->getGithubRepository() ?? '';
                $this->status            = $project->getStatus()->value;
            }
        }
    }

    #[LiveAction]
    public function save(): void
    {
        $this->validate();

        $customer = $this->customerRepository->findById($this->customerId);

        if ($customer === null) {
            return;
        }

        if ($this->projectId !== null && $this->existingProject !== null) {
            $project = $this->existingProject;
        } else {
            $project = new Project($this->name, $customer);
        }

        $project->setName($this->name);
        $project->setDescription($this->description !== '' ? $this->description : null);
        $project->setCustomer($customer);
        $project->setWebsiteUrl($this->websiteUrl !== '' ? $this->websiteUrl : null);
        $project->setGithubRepository($this->githubRepository !== '' ? $this->githubRepository : null);
        $project->setStatus(ProjectStatus::from($this->status));

        $this->projectRepository->save($project);
    }
}
