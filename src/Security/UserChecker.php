<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Security\Exception\AccountDisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserChecker
 *
 * @package App\Security
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * UserChecker constructor.
     */
    public function __construct()
    {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (! $user instanceof User) {
            return;
        }

        // user is deleted, show a generic Account Not Found message.
        if (!$user->isEnabled()) {
            throw new AccountDisabledException();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (! $user instanceof User) {
            return;
        }
    }
}
