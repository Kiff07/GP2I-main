<?php

namespace App\Controller;

use App\Entity\InventoryMovements;
use App\Entity\Users;
use App\Form\InventoryMovementsType;
use App\Repository\InventoryMovementsRepository;
use App\Service\StockManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/inventory-movements')]
class InventoryMovementsController extends AbstractController
{
    #[Route('/', name: 'app_inventory_movements_index', methods: ['GET'])]
    public function index(Request $request, InventoryMovementsRepository $repository): Response
    {
        $result = $repository->searchPaginated($request->query->get('q'), $request->query->get('type'), $request->query->getInt('page', 1));

        return $this->render('crud/index.html.twig', [
            'title' => 'Mouvements de stock',
            'new_path' => 'app_inventory_movements_new',
            'search_route' => 'app_inventory_movements_index',
            'search_q' => $request->query->get('q'),
            'filter_name' => 'type',
            'filter_label' => 'Type',
            'filter_choices' => InventoryMovements::typeChoices(),
            'filter_value' => $request->query->get('type'),
            'total' => $result['total'],
            'page' => $result['page'],
            'pages' => $result['pages'],
            'headers' => ['Matériel', 'Type', 'Quantité', 'De', 'Vers', 'Date'],
            'rows' => array_map(fn (InventoryMovements $movement) => [
                'entity' => $movement,
                'show_path' => 'app_inventory_movements_show',
                'edit_path' => 'app_inventory_movements_edit',
                'values' => [$movement->getMaterial()?->getName(), $movement->getTypeLabel(), $movement->getQuantity(), $movement->getFromLocation() ?: '-', $movement->getToLocation() ?: '-', $movement->getPerformedAt()?->format('d/m/Y')],
            ], $result['items']),
        ]);
    }

    #[Route('/new', name: 'app_inventory_movements_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function new(Request $request, EntityManagerInterface $em, StockManager $stockManager): Response
    {
        $movement = new InventoryMovements();
        $form = $this->createForm(InventoryMovementsType::class, $movement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // L'utilisateur connecté est l'auteur par defaut du mouvement.
            $user = $this->getUser();
            if (null === $movement->getPerformedBy() && $user instanceof Users) {
                $movement->setPerformedBy($user);
            }

            try {
                // CORRECTION : le mouvement met maintenant a jour le stock,
                // le lieu et le statut du matériel (avant : aucun effet).
                $stockManager->apply($movement);
            } catch (\DomainException|\InvalidArgumentException $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->render('crud/form.html.twig', ['title' => 'Nouveau mouvement', 'form' => $form, 'back_path' => 'app_inventory_movements_index']);
            }

            $em->persist($movement);
            $em->flush();
            $this->addFlash('success', 'Mouvement créé, stock mis a jour.');

            return $this->redirectToRoute('app_inventory_movements_index');
        }

        return $this->render('crud/form.html.twig', ['title' => 'Nouveau mouvement', 'form' => $form, 'back_path' => 'app_inventory_movements_index']);
    }

    #[Route('/{id}', name: 'app_inventory_movements_show', methods: ['GET'])]
    public function show(InventoryMovements $movement): Response
    {
        return $this->render('crud/show.html.twig', ['title' => (string) $movement, 'edit_path' => 'app_inventory_movements_edit', 'back_path' => 'app_inventory_movements_index', 'delete_path' => 'app_inventory_movements_delete', 'entity' => $movement, 'fields' => ['Matériel' => $movement->getMaterial()?->getName(), 'Type' => $movement->getTypeLabel(), 'Quantité' => $movement->getQuantity(), 'De' => $movement->getFromLocation() ?: '-', 'Vers' => $movement->getToLocation() ?: '-', 'Effectué par' => $movement->getPerformedBy()?->getFullName() ?: '-', 'Date' => $movement->getPerformedAt()?->format('d/m/Y'), 'Motif' => $movement->getReason() ?: '-']]);
    }

    #[Route('/{id}/edit', name: 'app_inventory_movements_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function edit(Request $request, InventoryMovements $movement, EntityManagerInterface $em): Response
    {
        // NB : l'edition corrige l'historique (motif, date...) mais ne
        // re-applique PAS le mouvement au stock, sinon il serait compte
        // deux fois. Pour corriger une quantite, creez un mouvement inverse.
        $form = $this->createForm(InventoryMovementsType::class, $movement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { $em->flush(); $this->addFlash('success', 'Mouvement modifié.'); return $this->redirectToRoute('app_inventory_movements_index'); }

        return $this->render('crud/form.html.twig', ['title' => 'Modifier mouvement', 'form' => $form, 'back_path' => 'app_inventory_movements_index']);
    }

    #[Route('/{id}', name: 'app_inventory_movements_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function delete(Request $request, InventoryMovements $movement, EntityManagerInterface $em): Response
    {
        // NB : supprimer un mouvement ne "defait" pas son effet sur le stock.
        // Créez un mouvement inverse si vous voulez annuler l'operation.
        if ($this->isCsrfTokenValid('delete'.$movement->getId(), (string) $request->request->get('_token'))) {
            $em->remove($movement);
            $em->flush();
            $this->addFlash('success', 'Mouvement supprimé.');
        }

        return $this->redirectToRoute('app_inventory_movements_index', [], Response::HTTP_SEE_OTHER);
    }
}
