<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Authorization\Entity\Permission;
use App\Domain\Authorization\Repository\ProjectMemberRepositoryInterface;
use App\Domain\Identity\Entity\User;
use App\Domain\Project\Entity\Project;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Handles all Permission enum values as Symfony attributes.
 * Subject may be a Project entity for project-scoped checks.
 *
 * @extends Voter<string, mixed>
 */
final class PermissionVoter extends Voter
{
    public function __construct(
        private readonly ProjectMemberRepositoryInterface $projectMemberRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return Permission::tryFrom($attribute) !== null;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $permission = Permission::from($attribute);
        $role = $user->getRole();

        if ($role === null) {
            return false;
        }

        // System role (Admin) has every permission
        if ($role->isSystem()) {
            return true;
        }

        // Global role permission
        if ($role->hasPermission($permission)) {
            return true;
        }

        // Project-scoped permission via membership
        if ($subject instanceof Project) {
            $member = $this->projectMemberRepository->findByUserAndProject($user, $subject);

            return $member !== null && $member->hasPermission($permission);
        }

        return false;
    }
}
