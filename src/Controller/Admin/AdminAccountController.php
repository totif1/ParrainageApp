<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Form\AdminAccountType;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Création de comptes administrateur — réservée au(x) super-admin(s).
 * Double verrou : access_control (security.yaml) ET l'attribut ci-dessous,
 * pour que la page reste protégée même si la config d'access_control
 * venait à changer.
 */
#[Route('/admin/comptes')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class AdminAccountController extends AbstractController
{
    #[Route('/nouveau', name: 'admin_account_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        AdminRepository $admins,
    ): Response {
        $admin = new Admin();
        $form = $this->createForm(AdminAccountType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $admin->setPasswordHash($passwordHasher->hashPassword($admin, $plainPassword));

            $em->persist($admin);
            $em->flush();

            $this->addFlash('success', \sprintf('Compte administrateur créé pour « %s ».', $admin->getUsername()));

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/account_new.html.twig', [
            'form' => $form,
            'admins' => $admins->findBy([], ['createdAt' => 'DESC']),
        ]);
    }
}
