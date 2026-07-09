<?php
/**
 * @package       SW JProjects
 * @subpackage  com_swjprojects
 *
 * @copyright  Copyright (c) 2018 - 2026 Sergey Tolkachyov. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

namespace Joomla\Component\SWJProjects\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use function in_array;

class AccessService
{
    public function __construct(private DatabaseInterface $db)
    {
    }

    public function canViewOwnUserKeys(User $user): bool
    {
        if ($this->canViewAllUserKeys($user)) {
            return true;
        }

        if ($user->guest) {
            return false;
        }

        return $user->authorise('swjprojects.userkeys.view.own', 'com_swjprojects')
            || $user->authorise('core.login.site', 'root.1')
            || $user->authorise('core.login.admin', 'root.1');
    }

    public function canViewAllUserKeys(User $user): bool
    {
        return in_array(8, $user->getAuthorisedGroups(), true)
            || $user->authorise('core.admin')
            || $user->authorise('core.admin', 'root.1')
            || $user->authorise('core.admin', 'com_swjprojects')
            || $user->authorise('core.manage', 'com_swjprojects')
            || $user->authorise('swjprojects.userkeys.view.all', 'com_swjprojects');
    }

    public function canViewKeyRecord(User $user, int $ownerId): bool
    {
        if ($this->canViewAllUserKeys($user)) {
            return true;
        }

        if ($ownerId <= 0 || !$this->canViewOwnUserKeys($user)) {
            return false;
        }

        return (int) $user->id === $ownerId;
    }

    public function getPermittedUserId(User $user, ?int $requestedUserId = null): ?int
    {
        if ($this->canViewAllUserKeys($user)) {
            return $requestedUserId && $requestedUserId > 0 ? $requestedUserId : null;
        }

        if (!$this->canViewOwnUserKeys($user)) {
            return -1;
        }

        return (int) $user->id;
    }
}
