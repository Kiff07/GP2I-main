<?php

namespace App\Controller;

use App\Entity\Materials;
use App\Form\MaterialsType;
use App\Repository\CategoriesRepository;
use App\Repository\MaterialsRepository;
use App\Repository\SuppliersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/materials')]
class MaterialsController extends AbstractController
{
    #[Route('/', name: 'app_materials_index', methods: ['GET'])]
    public function index(
        Request $request,
        MaterialsRepository $materialsRepository,
        CategoriesRepository $categoriesRepository,
        SuppliersRepository $suppliersRepository
    ): Response {
        $q = $request->query->get('q');
        $status = $request->query->get('status');
        $categoryId = $request->query->getInt('category');
        $supplierId = $request->query->getInt('supplier');
        $page = $request->query->getInt('page', 1);

        // Pagination : avant, search() chargeait toute la table d'un coup.
        $result = $materialsRepository->searchPaginated($q, $status, $categoryId ?: null, $supplierId ?: null, $page);

        return $this->render('materials/index.html.twig', [
            'materials' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'pages' => $result['pages'],
            'categories' => $categoriesRepository->findBy([], ['name' => 'ASC']),
            'suppliers' => $suppliersRepository->findBy([], ['name' => 'ASC']),
            'statusChoices' => Materials::statusChoices(),
            'filters' => [
                'q' => $q,
                'status' => $status,
                'category' => $categoryId,
                'supplier' => $supplierId,
            ],
        ]);
    }

    // Ecritures reservees aux gestionnaires (et admins par heritage de roles).
    #[Route('/new', name: 'app_materials_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $material = new Materials();
        $form = $this->createForm(MaterialsType::class, $material);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($material);
            $entityManager->flush();

            $this->addFlash('success', 'Materiel cree avec succes.');

            return $this->redirectToRoute('app_materials_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('materials/new.html.twig', [
            'material' => $material,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_materials_show', methods: ['GET'])]
    public function show(Materials $material): Response
    {
        return $this->render('materials/show.html.twig', [
            'material' => $material,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_materials_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function edit(Request $request, Materials $material, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MaterialsType::class, $material);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $material->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Materiel modifie avec succes.');

            return $this->redirectToRoute('app_materials_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('materials/edit.html.twig', [
            'material' => $material,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_materials_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function delete(Request $request, Materials $material, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$material->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($material);
            $entityManager->flush();
            $this->addFlash('success', 'Materiel supprime.');
        }

        return $this->redirectToRoute('app_materials_index', [], Response::HTTP_SEE_OTHER);
    }
}
