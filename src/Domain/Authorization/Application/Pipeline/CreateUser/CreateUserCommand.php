<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Application\Pipeline\CreateUser;

use App\Domain\Authorization\Application\Data\UserAdminData;
use App\Domain\Identity\Entity\User;
use App\Infrastructure\Pipeline\PipelineCommandInterface;

final class CreateUserCommand implements PipelineCommandInterface
{
    public ?User $result = null;

    public function __construct(
        public readonly UserAdminData $data,
    ) {
    }

    public function getEntityToSave(): object
    {
        assert($this->result !== null);

        return $this->result;
    }
}
