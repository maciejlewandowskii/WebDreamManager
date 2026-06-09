<?php

declare(strict_types=1);

namespace App\UI\Component\Project;

use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ProjectList
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $search = '';

    public function __construct(
        private readonly ProjectRepositoryInterface $repository,
    ) {
    }

    /** @return Project[] */
    public function getProjects(): array
    {
        if ($this->search !== '') {
            return $this->repository->search($this->search);
        }

        return $this->repository->findAll();
    }
}
