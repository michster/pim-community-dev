<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\User\Fake;

use Akeneo\Connectivity\Connection\Application\Settings\Service\UpdateUserPermissionsInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateUserPermissions implements UpdateUserPermissionsInterface
{
    public function execute(UserId $userId, int $userRoleId, ?int $userGroupId): void
    {
    }
}
