<?php

declare(strict_types=1);

namespace App\Domain\Identity\Infrastructure;

use App\Domain\Identity\Entity\PasskeyCredential;
use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Repository\PasskeyCredentialRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePasskeyCredentialRepository implements PasskeyCredentialRepositoryInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function save(PasskeyCredential $credential): void
    {
        $this->em->persist($credential);
        $this->em->flush();
    }

    public function remove(PasskeyCredential $credential): void
    {
        $this->em->remove($credential);
        $this->em->flush();
    }

    public function findByUser(User $user): array
    {
        return $this->em->getRepository(PasskeyCredential::class)
            ->findBy(['user' => $user], ['createdAt' => 'ASC']);
    }

    public function findById(string $id): ?PasskeyCredential
    {
        return $this->em->getRepository(PasskeyCredential::class)->find($id);
    }

    public function findByCredentialId(string $credentialId): ?PasskeyCredential
    {
        return $this->em->getRepository(PasskeyCredential::class)
            ->findOneBy(['credentialId' => $credentialId]);
    }
}
