<?php

declare(strict_types=1);

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\PasskeyCredential;
use App\Domain\Identity\Entity\User;

interface PasskeyCredentialRepositoryInterface
{
    public function save(PasskeyCredential $credential): void;
    public function remove(PasskeyCredential $credential): void;

    /** @return PasskeyCredential[] */
    public function findByUser(User $user): array;

    public function findById(string $id): ?PasskeyCredential;
    public function findByCredentialId(string $credentialId): ?PasskeyCredential;
}
