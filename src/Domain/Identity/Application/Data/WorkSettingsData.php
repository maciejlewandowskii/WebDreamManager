<?php

declare(strict_types=1);

namespace App\Domain\Identity\Application\Data;

use App\Domain\Identity\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

final class WorkSettingsData
{
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 24)]
    public ?int $workingHoursPerDay = 8;

    public static function fromUser(User $user): self
    {
        $data = new self();
        $data->workingHoursPerDay = $user->getWorkingHoursPerDay();

        return $data;
    }

    public function applyTo(User $user): void
    {
        $user->setWorkingHoursPerDay($this->workingHoursPerDay ?? 8);
    }
}
