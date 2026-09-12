<?php

namespace App\Tests\Functional;

use App\Repository\AdminRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MyAccountTest extends WebTestCase
{
    public function testAnonymousIsRedirectedToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/mon-compte');

        self::assertResponseRedirects('/admin/login');
    }

    public function testPageShowsOwnAccountInfo(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/mon-compte');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'admin');
        self::assertSelectorTextContains('body', 'Compte principal');
    }

    public function testWrongCurrentPasswordIsRejectedAndChangesNothing(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/mon-compte');
        $client->submitForm('Mettre à jour le mot de passe →', [
            'change_password[currentPassword]' => 'ce-nest-pas-le-bon',
            'change_password[newPassword][first]' => 'nouveaumdp123',
            'change_password[newPassword][second]' => 'nouveaumdp123',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'Mot de passe actuel incorrect');
        self::assertTrue($this->adminPasswordIs('admin123'));
    }

    public function testMismatchedNewPasswordsAreRejectedAndChangeNothing(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/mon-compte');
        $client->submitForm('Mettre à jour le mot de passe →', [
            'change_password[currentPassword]' => 'admin123',
            'change_password[newPassword][first]' => 'nouveaumdp123',
            'change_password[newPassword][second]' => 'autrechose456',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertTrue($this->adminPasswordIs('admin123'));
    }

    public function testValidPasswordChangeReplacesTheOldOne(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/mon-compte');
        $client->submitForm('Mettre à jour le mot de passe →', [
            'change_password[currentPassword]' => 'admin123',
            'change_password[newPassword][first]' => 'nouveaumdp123',
            'change_password[newPassword][second]' => 'nouveaumdp123',
        ]);

        self::assertResponseRedirects('/admin/mon-compte');
        self::assertTrue($this->adminPasswordIs('nouveaumdp123'));
        self::assertFalse($this->adminPasswordIs('admin123'));
    }

    private function adminPasswordIs(string $plainPassword): bool
    {
        $container = static::getContainer();
        $admin = $container->get(AdminRepository::class)->findOneBy(['username' => 'admin']);
        self::assertNotNull($admin);

        return $container->get(UserPasswordHasherInterface::class)->isPasswordValid($admin, $plainPassword);
    }

    private function loginAsAdmin(object $client): void
    {
        $admin = static::getContainer()->get(AdminRepository::class)->findOneBy(['username' => 'admin']);
        self::assertNotNull($admin, 'Le compte admin de démonstration doit exister (migration).');
        $client->loginUser($admin);
    }
}
