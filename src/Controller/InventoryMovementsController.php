<?php

namespace App\Controller;

use App\Entity\InventoryMovements;
use App\Form\InventoryMovementsType;
use App\Repository\InventoryMovementsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/inventory-movements')]
class InventoryMovementsController extends AbstractController
{
    #[Route('/', name: 'app_inventory_movements_index', methods: ['GET'])]
    public function index(InventoryMovementsRepository $repository): Response
    {
        return $this->render('crud/index.html.twig', [
            'title' => 'Mouvements de stock',
            'new_path' => 'app_inventory_movements_new',
            'headers' => ['Materiel', 'Type', 'Quantite', 'De', 'Vers', 'Date'],
            'rows' => array_map(fn (InventoryMovements $movement) => [
                'entity' => $movement,
                'show_path' => 'app_inventory_movements_show',
                'edit_path' => 'app_inventory_movements_edit',
                'values' => [$movement->getMaterial()?->getName(), $movement->getTypeLabel(), $movement->getQuantity(), $movement->getFromLocation() ?: '-', $movement->getToLocation() ?: '-', $movement->getPerformedAt()?->format('d/m/Y')],
            ], $repository->findBy([], ['id' => 'DESC'])),
        ]);
    }

    #[Route('/new', name: 'app_inventory_movements_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $movement = new InventoryMovements();
        $form = $this->createForm(InventoryMovementsType::class, $movement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { $em->persist($movement); $em->flush(); $this->addFlash('success', 'Mouvement cree.'); return $this->redirectToRoute('app_inventory_movements_index'); }
        return $this->render('crud/form.html.twig', ['title' => 'Nouveau mouvement', 'form' => $form, 'back_path' => 'app_inventory_movements_index']);
    }

    #[Route('/{id}', name: 'app_inventory_movements_show', methods: ['GET'])]
    public function show(InventoryMovements $movement): Response
    {
        return $this->render('crud/show.html.twig', ['title' => (string) $movement, 'edit_path' => 'app_inventory_movements_edit', 'back_path' => 'app_inventory_movements_index', 'entity' => $movement, 'fields' => ['Materiel' => $movement->getMaterial()?->getName(), 'Type' => $movement->getTypeLabel(), 'Quantite' => $movement->getQuantity(), 'De' => $movement->getFromLocation() ?: '-', 'Vers' => $movement->getToLocation() ?: '-', 'Effectue par' => $movement->getPerformedBy()?->getFullName() ?: '-', 'Date' => $movement->getPerformedAt()?->format('d/m/Y'), 'Motif' => $movement->getReason() ?: '-']]);
    }

    #[Route('/{id}/edit', name: 'app_inventory_movements_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, InventoryMovements $movement, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(InventoryMovementsType::class, $movement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { $em->flush(); $this->addFlash('success', 'Mouvement modifie.'); return $this->redirectToRoute('app_inventory_movements_index'); }
        return $this->render('crud/form.html.twig', ['title' => 'Modifier mouvement', 'form' => $form, 'back_path' => 'app_inventory_movements_index']);
    }
}
