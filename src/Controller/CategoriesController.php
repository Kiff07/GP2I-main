<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Form\CategoriesType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/categories')]
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'app_categories_index', methods: ['GET'])]
    public function index(Request $request, CategoriesRepository $categoriesRepository): Response
    {
        $q = $request->query->get('q');

        return $this->render('categories/index.html.twig', [
            'categories' => $categoriesRepository->search($q),
            'q' => $q,
        ]);
    }

    #[Route('/new', name: 'app_categories_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Categories();
        $form = $this->createForm(CategoriesType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', 'Categorie creee avec succes.');

            return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categories/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categories_show', methods: ['GET'])]
    public function show(Categories $category): Response
    {
        return $this->render('categories/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categories_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function edit(Request $request, Categories $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoriesType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Categorie modifiee avec succes.');

            return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categories/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categories_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function delete(Request $request, Categories $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), (string) $request->request->get('_token'))) {
            if ($category->getMaterials()->count() > 0) {
                $this->addFlash('warning', 'Cette categorie contient encore du materiel.');
            } else {
                $entityManager->remove($category);
                $entityManager->flush();
                $this->addFlash('success', 'Categorie supprimee.');
            }
        }

        return $this->redirectToRoute('app_categories_index', [], Response::HTTP_SEE_OTHER);
    }
}
