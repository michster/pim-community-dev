<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Response;

class InternalApiClient
{
    /** @var AbstractBrowser */
    private $browser;

    /** @var SessionInterface */
    private $session;

    public function __construct(AbstractBrowser $browser, SessionInterface $session)
    {
        $this->browser = $browser;
        $this->session = $session;
    }

    public function authenticate(UserInterface $user): void
    {
        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $this->session->set('_security_' . $firewallContext, serialize($token));
        $this->session->save();

        $cookie = new Cookie($this->session->getName(), $this->session->getId());
        $this->browser->getCookieJar()->set($cookie);
    }

    public function post(string $url, array $data): Response
    {
        $this->browser->request(
            'POST',
            $url,
            [],
            [],
            ['Content-Type' => 'application/json'],
            json_encode($data)
        );

        return $this->browser->getResponse();
    }
}
