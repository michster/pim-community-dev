<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Doctrine\DBAL\Connection as DbalConnection;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateConnectionEndToEnd extends WebTestCase
{
    public function test_it_creates_a_connection(): void
    {
        $data = [
            "code" => "franklin",
            "label" => "Franklin with updated label",
            "flow_type" => FlowType::DATA_DESTINATION,
        ];

        $this->authenticateAsAdmin();
        $this->client->request(
            'POST',
            '/rest/connections',
            ['headers' => ['Content-Type' => 'application/json']],
            [],
            [],
            json_encode($data)
        );

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_creates_the_connection_and_client_and_user()
    {
        $createConnectionCommand = new CreateConnectionCommand('magento', 'Magento Connector', FlowType::DATA_DESTINATION);
        $connectionWithCredentials = $this->get('akeneo_connectivity.connection.application.handler.create_connection')->handle($createConnectionCommand);

        $this->get('doctrine.orm.entity_manager')->clear();

        Assert::assertInstanceOf(ConnectionWithCredentials::class, $connectionWithCredentials);
        Assert::assertEquals('magento', $connectionWithCredentials->code());
        Assert::assertEquals('Magento Connector', $connectionWithCredentials->label());
        Assert::assertEquals(FlowType::DATA_DESTINATION, $connectionWithCredentials->flowType());
        Assert::assertNull($connectionWithCredentials->image());

        Assert::assertEquals(1, $this->countConnection('magento'));
        Assert::assertEquals(1, $this->countClient($connectionWithCredentials->secret()));

        $user = $this->selectUser($connectionWithCredentials->username());
        $regex = sprintf('/^%s_[0-9]{4}$/', $connectionWithCredentials->code());
        Assert::assertSame(1, preg_match($regex, $user['username']));
        Assert::assertSame(1, preg_match('/@example.com$/', $user['email']));
        Assert::assertSame($connectionWithCredentials->label(), $user['first_name']);
        Assert::assertSame(' ', $user['last_name']);
        Assert::assertSame(1, (int) $user['enabled']);
        Assert::assertSame(0, (int) $user['emailNotifications']);

        Assert::assertSame('ROLE_USER', $user['role']);
        Assert::assertSame('All', $user['group_name']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function countConnection(string $connectionCode): int
    {
        $selectSql = <<<SQL
SELECT count(code) FROM akeneo_connectivity_connection WHERE code = :code
SQL;
        $stmt = $this->getDbalConnection()->executeQuery($selectSql, ['code' => $connectionCode]);

        return (int) $stmt->fetchColumn();
    }

    private function countClient(string $secret): int
    {
        $selectSql = <<<SQL
SELECT count(id) FROM pim_api_client WHERE secret = :secret
SQL;
        $stmt = $this->getDbalConnection()->executeQuery($selectSql, ['secret' => $secret]);

        return (int) $stmt->fetchColumn();
    }

    private function selectUser(string $username): array
    {
        $selectSql = <<<SQL
SELECT u.id, u.username, u.first_name, u.last_name, u.email, u.enabled, u.emailNotifications, r.role, g.name as group_name
FROM oro_user u
INNER JOIN oro_user_access_role ur ON ur.user_id = u.id
INNER JOIN oro_access_role r ON r.id = ur.role_id
INNER JOIN oro_user_access_group ug ON ug.user_id = u.id
INNER JOIN oro_access_group g ON g.id = ug.group_id
WHERE u.username = :username
SQL;
        $stmt = $this->getDbalConnection()->executeQuery($selectSql, ['username' => $username]);

        return $stmt->fetchAll()[0];
    }

    private function getDbalConnection(): DbalConnection
    {
        return $this->get('database_connection');
    }
}
