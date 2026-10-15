<?php

namespace App\Controller;

use App\Entity\Suppliers;
use App\Form\SuppliersType;
use App\Repository\SuppliersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/suppliers')]
class SuppliersController extends AbstractController
{
    #[Route('/', name: 'app_suppliers_index', methods: ['GET'])]
    public function index(Request $request, SuppliersRepository $suppliersRepository): Response
    {
        $q = $request->query->get('q');

        return $this->render('suppliers/index.html.twig', [
            'suppliers' => $suppliersRepository->search($q),
            'q' => $q,
        ]);
    }

    #[Route('/new', name: 'app_suppliers_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $supplier = new Suppliers();
        $form = $this->createForm(SuppliersType::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($supplier);
            $entityManager->flush();

            $this->addFlash('success', 'Fournisseur créé avec succes.');

            return $this->redirectToRoute('app_suppliers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('suppliers/new.html.twig', [
            'supplier' => $supplier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_suppliers_show', methods: ['GET'])]
    public function show(Suppliers $supplier): Response
    {
        return $this->render('suppliers/show.html.twig', [
            'supplier' => $supplier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_suppliers_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function edit(Request $request, Suppliers $supplier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SuppliersType::class, $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Fournisseur modifié avec succes.');

            return $this->redirectToRoute('app_suppliers_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('suppliers/edit.html.twig', [
            'supplier' => $supplier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_suppliers_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function delete(Request $request, Suppliers $supplier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$supplier->getId(), (string) $request->request->get('_token'))) {
            if ($supplier->getMaterials()->count() > 0) {
                $this->addFlash('warning', 'Ce fournisseur est encore associé a du matériel.');
            } else {
                $entityManager->remove($supplier);
                $entityManager->flush();
                $this->addFlash('success', 'Fournisseur supprimé.');
            }
        }

        return $this->redirectToRoute('app_suppliers_index', [], Response::HTTP_SEE_OTHER);
    }
}
