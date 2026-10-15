<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Profil de l'utilisateur connecté + changement de mot de passe.
 */
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $current = (string) $form->get('currentPassword')->getData();
            $new = (string) $form->get('newPassword')->getData();

            if (!$hasher->isPasswordValid($user, $current)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
            } elseif (mb_strlen($new) < 8) {
                $this->addFlash('error', 'Le nouveau mot de passe doit contenir au moins 8 caractères.');
            } else {
                $user->setPassword($hasher->hashPassword($user, $new));
                $user->setUpdatedAt(new \DateTime());
                $em->flush();
                $this->addFlash('success', 'Mot de passe modifié.');

                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
