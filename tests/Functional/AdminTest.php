<?php

namespace App\Tests\Functional;

use App\Entity\Inscription;
use App\Repository\AdminRepository;
use App\Repository\InscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminTest extends WebTestCase
{
    public function testDashboardRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        self::assertResponseRedirects('/admin/login');
    }

    public function testDashboardListsInscriptionsWhenLoggedIn(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Tableau de bord');
        self::assertSelectorTextContains('body', 'jean.dupont@example.com');
    }

    public function testExportReturnsCsv(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/admin/export');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/csv; charset=utf-8');

        // C'est un StreamedResponse : BrowserKit capture ce qui a été envoyé
        // dans la "réponse interne", pas dans getResponse()->getContent().
        $csv = $client->getInternalResponse()->getContent();

        self::assertStringContainsString('id,nom,prenom,email', $csv);
        self::assertStringContainsString('jean.dupont@example.com', $csv);
    }

    public function testDeleteRemovesInscription(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
        $container = static::getContainer();

        /** @var InscriptionRepository $repository */
        $repository = $container->get(InscriptionRepository::class);
        $target = $repository->findOneBy(['email' => 'sophie.martin@example.com']);
        self::assertInstanceOf(Inscription::class, $target);
        $id = $target->getId();

        // On soumet le vrai formulaire "Supprimer" de la ligne visée :
        // le crawler récupère le jeton CSRF valide lié à la session.
        $crawler = $client->request('GET', '/admin');
        $form = $crawler
            ->filter('form[action$="/admin/inscriptions/'.$id.'/supprimer"]')
            ->selectButton('Supprimer')
            ->form();
        $client->submit($form);

        self::assertResponseRedirects('/admin');
        self::assertNull($repository->find($id));
    }

    private function loginAsAdmin(object $client): void
    {
        $admin = static::getContainer()->get(AdminRepository::class)->findOneBy(['username' => 'admin']);
        self::assertNotNull($admin, 'Le compte admin de démonstration doit exister (migration).');
        $client->loginUser($admin);
    }
}
