<?php

namespace App\Controller;

use App\Entity\PurchaseItems;
use App\Form\PurchaseItemsType;
use App\Repository\PurchaseItemsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/purchase-items')]
class PurchaseItemsController extends AbstractController
{
    #[Route('/', name: 'app_purchase_items_index', methods: ['GET'])]
    public function index(PurchaseItemsRepository $repository): Response
    {
        return $this->render('crud/index.html.twig', [
            'title' => 'Lignes de commande',
            'new_path' => 'app_purchase_items_new',
            'headers' => ['Commande', 'Materiel', 'Reference', 'Quantite', 'Recue'],
            'rows' => array_map(fn (PurchaseItems $item) => [
                'entity' => $item,
                'show_path' => 'app_purchase_items_show',
                'edit_path' => 'app_purchase_items_edit',
                'values' => [$item->getPurchaseOrder()?->getOrderNumber(), $item->getMaterialName(), $item->getMaterialReference() ?: '-', $item->getQuantity(), $item->getReceivedQuantity()],
            ], $repository->findBy([], ['id' => 'DESC'])),
        ]);
    }

    #[Route('/new', name: 'app_purchase_items_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $item = new PurchaseItems();
        $form = $this->createForm(PurchaseItemsType::class, $item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { $em->persist($item); $em->flush(); $this->addFlash('success', 'Ligne creee.'); return $this->redirectToRoute('app_purchase_items_index'); }
        return $this->render('crud/form.html.twig', ['title' => 'Nouvelle ligne', 'form' => $form, 'back_path' => 'app_purchase_items_index']);
    }

    #[Route('/{id}', name: 'app_purchase_items_show', methods: ['GET'])]
    public function show(PurchaseItems $item): Response
    {
        return $this->render('crud/show.html.twig', ['title' => (string) $item, 'edit_path' => 'app_purchase_items_edit', 'back_path' => 'app_purchase_items_index', 'entity' => $item, 'fields' => ['Commande' => $item->getPurchaseOrder()?->getOrderNumber(), 'Materiel' => $item->getMaterialName(), 'Reference' => $item->getMaterialReference() ?: '-', 'Quantite' => $item->getQuantity(), 'Prix unitaire' => $item->getUnitPrice() ?: '-', 'Quantite recue' => $item->getReceivedQuantity()]]);
    }

    #[Route('/{id}/edit', name: 'app_purchase_items_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PurchaseItems $item, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PurchaseItemsType::class, $item);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { $em->flush(); $this->addFlash('success', 'Ligne modifiee.'); return $this->redirectToRoute('app_purchase_items_index'); }
        return $this->render('crud/form.html.twig', ['title' => 'Modifier ligne', 'form' => $form, 'back_path' => 'app_purchase_items_index']);
    }
}
