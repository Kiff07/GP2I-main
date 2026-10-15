<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// La gestion des comptes est reservee aux administrateurs
// (double protection avec access_control dans security.yaml).
#[Route('/users')]
#[IsGranted('ROLE_ADMIN')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'app_users_index', methods: ['GET'])]
    public function index(UsersRepository $repository): Response
    {
        return $this->render('crud/index.html.twig', [
            'title' => 'Utilisateurs',
            'new_path' => 'app_users_new',
            'headers' => ['Identifiant', 'Email', 'Nom complet', 'Téléphone'],
            'rows' => array_map(fn (Users $user) => [
                'entity' => $user,
                'show_path' => 'app_users_show',
                'edit_path' => 'app_users_edit',
                'values' => [$user->getUsername(), $user->getEmail(), $user->getFullName() ?: '-', $user->getPhone() ?: '-'],
            ], $repository->findBy([], ['username' => 'ASC'])),
        ]);
    }

    #[Route('/new', name: 'app_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user, ['password_required' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            if (mb_strlen($plainPassword) < 8) {
                $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');

                return $this->render('crud/form.html.twig', ['title' => 'Nouvel utilisateur', 'form' => $form, 'back_path' => 'app_users_index']);
            }
            $user->setPassword($hasher->hashPassword($user, $plainPassword));
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur créé.');

            return $this->redirectToRoute('app_users_index');
        }

        return $this->render('crud/form.html.twig', ['title' => 'Nouvel utilisateur', 'form' => $form, 'back_path' => 'app_users_index']);
    }

    #[Route('/{id}', name: 'app_users_show', methods: ['GET'])]
    public function show(Users $user): Response
    {
        return $this->render('crud/show.html.twig', [
            'title' => (string) $user,
            'edit_path' => 'app_users_edit',
            'back_path' => 'app_users_index',
            'delete_path' => 'app_users_delete',
            'entity' => $user,
            'fields' => [
                'Identifiant' => $user->getUsername(),
                'Email' => $user->getEmail(),
                'Nom complet' => $user->getFullName() ?: '-',
                'Téléphone' => $user->getPhone() ?: '-',
                'Rôles' => implode(', ', $user->getRoles()),
            ],
        ]);
    }

    #[Route('/{id}/edit', name: 'app_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Users $user, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(UsersType::class, $user, ['password_required' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // On ne touche au mot de passe QUE si un nouveau est saisi.
            $plainPassword = $form->get('plainPassword')->getData();
            if (is_string($plainPassword) && '' !== $plainPassword) {
                if (mb_strlen($plainPassword) < 8) {
                    $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');

                    return $this->render('crud/form.html.twig', ['title' => 'Modifier utilisateur', 'form' => $form, 'back_path' => 'app_users_index']);
                }
                $user->setPassword($hasher->hashPassword($user, $plainPassword));
            }
            $user->setUpdatedAt(new \DateTime());
            $em->flush();
            $this->addFlash('success', 'Utilisateur modifié.');

            return $this->redirectToRoute('app_users_index');
        }

        return $this->render('crud/form.html.twig', ['title' => 'Modifier utilisateur', 'form' => $form, 'back_path' => 'app_users_index']);
    }

    #[Route('/{id}', name: 'app_users_delete', methods: ['POST'])]
    public function delete(Request $request, Users $user, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), (string) $request->request->get('_token'))) {
            if ($user === $this->getUser()) {
                $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            } else {
                $em->remove($user);
                $em->flush();
                $this->addFlash('success', 'Utilisateur supprimé.');
            }
        }

        return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
    }
}
