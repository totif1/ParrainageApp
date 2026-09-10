<?php

namespace App\Tests\Functional;

use App\Repository\InscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InscriptionTest extends WebTestCase
{
    public function testValidSubmissionIsPersistedAndRedirects(): void
    {
        $client = static::createClient();
        $repository = static::getContainer()->get(InscriptionRepository::class);
        $before = $repository->count([]);

        $client->request('GET', '/inscription');
        $client->submitForm('Envoyer mon inscription →', [
            'inscription[prenom]' => 'Camille',
            'inscription[nom]' => 'Test',
            'inscription[email]' => 'camille.test@example.com',
            'inscription[classe]' => 'BUT1INFO',
            'inscription[preference]' => 'FILLEUL',
            'inscription[motivation]' => 'Test fonctionnel.',
        ]);

        self::assertResponseRedirects('/inscription/merci');
        self::assertSame($before + 1, $repository->count([]));

        $created = $repository->findOneBy(['email' => 'camille.test@example.com']);
        self::assertNotNull($created);
        self::assertSame('Camille', $created->getPrenom());
    }

    public function testInvalidSubmissionShowsErrorAndPersistsNothing(): void
    {
        $client = static::createClient();
        $repository = static::getContainer()->get(InscriptionRepository::class);
        $before = $repository->count([]);

        $client->request('GET', '/inscription');
        $client->submitForm('Envoyer mon inscription →', [
            'inscription[prenom]' => 'Sans',
            'inscription[nom]' => 'Email',
            'inscription[email]' => '',
            'inscription[classe]' => 'BUT1INFO',
            'inscription[preference]' => 'FILLEUL',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('.field-error');
        self::assertSame($before, $repository->count([]));
    }
}
