<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InscriptionController extends AbstractController
{
    #[Route('/inscription', name: 'inscription', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $em): Response
    {
        $inscription = new Inscription();
        $form = $this->createForm(InscriptionType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($inscription);
            $em->flush();

            $this->addFlash('success', 'Ton inscription est bien enregistrée. On revient vers toi très vite !');

            return $this->redirectToRoute('inscription_merci');
        }

        return $this->render('inscription/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/inscription/merci', name: 'inscription_merci', methods: ['GET'])]
    public function merci(): Response
    {
        return $this->render('inscription/merci.html.twig');
    }
}
