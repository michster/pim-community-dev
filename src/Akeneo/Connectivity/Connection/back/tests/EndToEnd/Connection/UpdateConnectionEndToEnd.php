<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\InternalApiTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateConnectionEndToEnd extends InternalApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->createAdminUser();
        $this->getInternalApiClient()->authenticate($user);
    }

    public function test_it_updates_a_connection(): void
    {
        /** @var ConnectionWithCredentials */
        $connection = $this->get('akeneo_connectivity.connection.fixtures.connection_loader')->createConnection(
            'franklin',
            'Franklin',
            FlowType::DATA_SOURCE
        );

        $data = [
            "code" => "franklin",
            "label" => "Franklin with updated label",
            "flow_type" => FlowType::DATA_DESTINATION,
            "image" => null,
            "user_role_id" => $connection->userRoleId(),
            "user_group_id" => $connection->userGroupId()
        ];

        $response = $this->getInternalApiClient()->post('/rest/connections/franklin', $data);

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function test_it_fails_to_update_a_connection_with_a_bad_request(): void
    {
        /** @var ConnectionWithCredentials */
        $connection = $this->get('akeneo_connectivity.connection.fixtures.connection_loader')->createConnection(
            'franklin',
            'Franklin',
            FlowType::DATA_SOURCE
        );

        $data = [
            "code" => "franklin",
            "label" => "",
            "flow_type" => 'wrong_flow_type',
            "image" => null,
            "user_role_id" => $connection->userRoleId(),
            "user_group_id" => $connection->userGroupId()
        ];

        $response = $this->getInternalApiClient()->post('/rest/connections/franklin', $data);

        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $result = [
            "message" => "akeneo_connectivity.connection.constraint_violation_list_exception",
            "errors" => [
                [
                    "name" => "label",
                    "reason" => "akeneo_connectivity.connection.connection.constraint.label.required"
                ],
                [
                    "name" => "flowType",
                    "reason" => "akeneo_connectivity.connection.connection.constraint.flow_type.invalid"
                ]
            ]
        ];
        Assert::assertEquals($result, json_decode($response->getContent(), true));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
