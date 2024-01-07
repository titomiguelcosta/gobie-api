<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Job;
use App\Entity\User;
use App\Security\Permissions;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class JobRerunVoter extends Voter
{
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [Permissions::JOB_RERUN]) && $subject instanceof Job;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return $user->isAdmin() || $subject->getProject()->getCreatedBy()->getId() === $user->getId();
    }
}
