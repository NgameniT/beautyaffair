<?php

namespace App\Tests\Unit;

use App\Entity\User;
use App\Security\AuthentificationAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class LoginRedirectTest extends TestCase
{
    public function testAdminIsRedirectedToAdminDashboard(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnMap([
            ['app_admin_dashboard', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/admin'],
            ['app_librarian_dashboard', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/bibliothecaire'],
            ['app_dashboard', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/mon-compte'],
            ['app_login', [], UrlGeneratorInterface::ABSOLUTE_PATH, '/login'],
        ]);

        $authenticator = new AuthentificationAuthenticator($urlGenerator);

        $user = (new User())->setEmail('admin@example.com')->setRoles(['ROLE_ADMIN']);
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $response = $authenticator->onAuthenticationSuccess($request, $token, 'main');

        self::assertNotNull($response);
        self::assertSame('/admin', $response->headers->get('Location'));
    }
}
