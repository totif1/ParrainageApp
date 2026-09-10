<?php

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PublicPagesTest extends WebTestCase
{
    /**
     * @return iterable<string, array{string, int}>
     */
    public static function publicUrls(): iterable
    {
        yield 'accueil' => ['/', 200];
        yield 'inscription' => ['/inscription', 200];
        yield 'merci' => ['/inscription/merci', 200];
        yield 'connexion admin' => ['/admin/login', 200];
    }

    #[DataProvider('publicUrls')]
    public function testPageIsReachable(string $url, int $expectedStatus): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        self::assertSame($expectedStatus, $client->getResponse()->getStatusCode());
    }

    public function testHomeShowsHeadline(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertSelectorTextContains('h1', 'Trouve ton');
    }

    public function testAdminAreaRedirectsAnonymousToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        self::assertResponseRedirects('/admin/login');
    }
}
