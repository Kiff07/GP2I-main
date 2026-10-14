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

#[Route('/users')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'app_users_index', methods: ['GET'])]
    public function index(UsersRepository $repository): Response
    {
        return $this->render('crud/index.html.twig', [
            'title' => 'Utilisateurs',
            'new_path' => 'app_users_new',
            'headers' => ['Identifiant', 'Email', 'Nom complet', 'Telephone'],
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
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($hasher->hashPassword($user, (string) $user->getPassword()));
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur cree.');

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
            'entity' => $user,
            'fields' => [
                'Identifiant' => $user->getUsername(),
                'Email' => $user->getEmail(),
                'Nom complet' => $user->getFullName() ?: '-',
                'Telephone' => $user->getPhone() ?: '-',
                'Roles' => implode(', ', $user->getRoles()),
            ],
        ]);
    }

    #[Route('/{id}/edit', name: 'app_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Users $user, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $currentPassword = $user->getPassword();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getPassword() !== $currentPassword) {
                $user->setPassword($hasher->hashPassword($user, (string) $user->getPassword()));
            }
            $user->setUpdatedAt(new \DateTime());
            $em->flush();
            $this->addFlash('success', 'Utilisateur modifie.');

            return $this->redirectToRoute('app_users_index');
        }

        return $this->render('crud/form.html.twig', ['title' => 'Modifier utilisateur', 'form' => $form, 'back_path' => 'app_users_index']);
    }
}
