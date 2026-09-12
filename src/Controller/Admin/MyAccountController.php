<?php

namespace App\Controller\Admin;

use App\Entity\Admin;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Page « Mon compte » : accessible à n'importe quel admin connecté (à la
 * différence de la création de comptes, réservée aux super-admins).
 * Permet à chacun de remplacer un mot de passe temporaire par le sien.
 */
class MyAccountController extends AbstractController
{
    #[Route('/admin/mon-compte', name: 'admin_my_account', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $admin = $this->getUser();
        if (!$admin instanceof Admin) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $currentPassword */
            $currentPassword = $form->get('currentPassword')->getData();

            if (!$passwordHasher->isPasswordValid($admin, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('Mot de passe actuel incorrect.'));
            } else {
                /** @var string $newPassword */
                $newPassword = $form->get('newPassword')->getData();
                $admin->setPasswordHash($passwordHasher->hashPassword($admin, $newPassword));
                $em->flush();

                $this->addFlash('success', 'Ton mot de passe a bien été mis à jour.');

                return $this->redirectToRoute('admin_my_account');
            }
        }

        return $this->render('admin/my_account.html.twig', [
            'admin' => $admin,
            'form' => $form,
        ]);
    }
}
