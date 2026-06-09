<?php

declare(strict_types=1);

namespace App\UI\Component\TimeTracking;

use App\Domain\Project\Repository\ProjectRepositoryInterface;
use App\Domain\TimeTracking\Entity\TimeRecord;
use App\Domain\TimeTracking\Repository\TimeRecordRepositoryInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TimeRecordList
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $search = '';

    #[LiveProp(writable: true)]
    public string $projectId = '';

    #[LiveProp(writable: true)]
    public bool $uninvoicedOnly = false;

    public function __construct(
        private readonly TimeRecordRepositoryInterface $recordRepository,
        private readonly ProjectRepositoryInterface $projectRepository,
    ) {
    }

    /** @return TimeRecord[] */
    public function getRecords(): array
    {
        $project = $this->projectId !== ''
            ? $this->projectRepository->findById($this->projectId)
            : null;

        return $this->recordRepository->findFiltered(
            search: $this->search !== '' ? $this->search : null,
            project: $project,
            uninvoicedOnly: $this->uninvoicedOnly,
        );
    }
}
