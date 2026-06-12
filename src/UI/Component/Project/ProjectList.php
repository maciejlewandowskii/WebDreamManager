<?php

declare(strict_types=1);

namespace App\UI\Component\Project;

use App\Domain\Project\Entity\Project;
use App\Domain\Project\Repository\ProjectRepositoryInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ProjectList
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $search = '';

    #[LiveProp(writable: true)]
    public string $sortBy = 'createdAt';

    #[LiveProp(writable: true)]
    public string $sortDirection = 'DESC';

    public function __construct(
        private readonly ProjectRepositoryInterface $repository,
    ) {
    }

    /** @return Project[] */
    public function getProjects(): array
    {
        if ($this->search !== '') {
            return $this->repository->search($this->search, $this->sortBy, $this->sortDirection);
        }

        return $this->repository->findAll($this->sortBy, $this->sortDirection);
    }

    #[LiveAction]
    public function sortBy(#[LiveArg] string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'ASC' ? 'DESC' : 'ASC';

            return;
        }

        $this->sortBy = $field;
        $this->sortDirection = 'ASC';
    }
}
