<?php

namespace App\Tests\Functional;

use App\Repository\AdminRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminAccountTest extends WebTestCase
{
    public function testAnonymousIsRedirectedToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/comptes/nouveau');

        self::assertResponseRedirects('/admin/login');
    }

    public function testFormIsReachableWhenLoggedIn(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/comptes/nouveau');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Nouveau compte admin');
        self::assertSelectorTextContains('h2', 'Créer un accès');
    }

    public function testValidSubmissionCreatesAnUsableAdminAccount(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $container = static::getContainer();

        /** @var AdminRepository $admins */
        $admins = $container->get(AdminRepository::class);
        $before = $admins->count([]);

        $client->request('GET', '/admin/comptes/nouveau');
        $client->submitForm('Créer le compte →', [
            'admin_account[username]' => 'nouvelle.recrue',
            'admin_account[plainPassword][first]' => 'motdepasse123',
            'admin_account[plainPassword][second]' => 'motdepasse123',
        ]);

        self::assertResponseRedirects('/admin');
        self::assertSame($before + 1, $admins->count([]));

        $created = $admins->findOneBy(['username' => 'nouvelle.recrue']);
        self::assertNotNull($created);

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($created, 'motdepasse123'));
        self::assertNotSame('motdepasse123', $created->getPasswordHash());
    }

    public function testPasswordMismatchIsRejected(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $admins = static::getContainer()->get(AdminRepository::class);
        $before = $admins->count([]);

        $client->request('GET', '/admin/comptes/nouveau');
        $client->submitForm('Créer le compte →', [
            'admin_account[username]' => 'oups',
            'admin_account[plainPassword][first]' => 'motdepasse123',
            'admin_account[plainPassword][second]' => 'autrechose456',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('.field-error');
        self::assertSame($before, $admins->count([]));
    }

    public function testDuplicateUsernameIsRejected(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $admins = static::getContainer()->get(AdminRepository::class);
        $before = $admins->count([]);

        $client->request('GET', '/admin/comptes/nouveau');
        $client->submitForm('Créer le compte →', [
            // "admin" existe déjà (compte de démo créé par la migration).
            'admin_account[username]' => 'admin',
            'admin_account[plainPassword][first]' => 'motdepasse123',
            'admin_account[plainPassword][second]' => 'motdepasse123',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSame($before, $admins->count([]));
    }

    private function loginAsAdmin(object $client): void
    {
        $admin = static::getContainer()->get(AdminRepository::class)->findOneBy(['username' => 'admin']);
        self::assertNotNull($admin, 'Le compte admin de démonstration doit exister (migration).');
        $client->loginUser($admin);
    }
}
