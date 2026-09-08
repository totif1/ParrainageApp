<?php

namespace App\Controller\Admin;

use App\Entity\Inscription;
use App\Enum\Classe;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function index(Request $request, InscriptionRepository $inscriptions): Response
    {
        $classe = Classe::tryFrom($request->query->get('classe', ''));
        $email = trim($request->query->get('email', ''));

        return $this->render('admin/dashboard.html.twig', [
            'inscriptions' => $inscriptions->findFiltered($classe, $email),
            'stats' => $inscriptions->statistics(),
            'filterClasse' => $classe,
            'filterEmail' => $email,
            'classes' => Classe::cases(),
        ]);
    }

    #[Route('/inscriptions/{id}/supprimer', name: 'admin_inscription_delete', methods: ['POST'])]
    public function delete(Request $request, Inscription $inscription, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_inscription_'.$inscription->getId(), $request->request->get('_token'))) {
            $em->remove($inscription);
            $em->flush();
            $this->addFlash('success', 'Inscription supprimée.');
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/export', name: 'admin_export', methods: ['GET'])]
    public function export(InscriptionRepository $inscriptions): StreamedResponse
    {
        $rows = $inscriptions->findFiltered(null, null);

        $response = new StreamedResponse(function () use ($rows) {
            $out = fopen('php://output', 'wb');
            fputcsv($out, ['id', 'nom', 'prenom', 'email', 'classe', 'preference', 'motivation', 'discord', 'insta', 'date_inscription'], ',', '"', '\\');
            foreach ($rows as $i) {
                fputcsv($out, [
                    $i->getId(),
                    $i->getNom(),
                    $i->getPrenom(),
                    $i->getEmail(),
                    $i->getClasse()?->value,
                    $i->getPreference()?->value,
                    $i->getMotivation(),
                    $i->getDiscord(),
                    $i->getInsta(),
                    $i->getDateInscription()->format('Y-m-d H:i:s'),
                ], ',', '"', '\\');
            }
            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="inscriptions.csv"');

        return $response;
    }
}
