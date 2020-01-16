<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd;

use Akeneo\Test\Integration\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class InternalApiTestCase extends TestCase
{
    protected function getInternalApiClient(): InternalApiClient
    {
        return $this->get(InternalApiClient::class);
    }
}
