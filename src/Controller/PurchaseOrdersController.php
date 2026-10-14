<?php

namespace App\Controller;

use App\Entity\PurchaseOrders;
use App\Form\PurchaseOrdersType;
use App\Repository\PurchaseOrdersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/purchase-orders')]
class PurchaseOrdersController extends AbstractController
{
    #[Route('/', name: 'app_purchase_orders_index', methods: ['GET'])]
    public function index(PurchaseOrdersRepository $repository): Response
    {
        return $this->render('crud/index.html.twig', [
            'title' => 'Commandes',
            'new_path' => 'app_purchase_orders_new',
            'headers' => ['Numero', 'Fournisseur', 'Statut', 'Date', 'Total'],
            'rows' => array_map(fn (PurchaseOrders $order) => [
                'entity' => $order,
                'show_path' => 'app_purchase_orders_show',
                'edit_path' => 'app_purchase_orders_edit',
                'values' => [$order->getOrderNumber(), $order->getSupplier()?->getName() ?: '-', $order->getStatusLabel(), $order->getOrderDate()?->format('d/m/Y'), $order->getTotalAmount() ?: '-'],
            ], $repository->findBy([], ['id' => 'DESC'])),
        ]);
    }

    #[Route('/new', name: 'app_purchase_orders_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $order = new PurchaseOrders();
        $form = $this->createForm(PurchaseOrdersType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($order);
            $em->flush();
            $this->addFlash('success', 'Commande creee.');
            return $this->redirectToRoute('app_purchase_orders_index');
        }
        return $this->render('crud/form.html.twig', ['title' => 'Nouvelle commande', 'form' => $form, 'back_path' => 'app_purchase_orders_index']);
    }

    #[Route('/{id}', name: 'app_purchase_orders_show', methods: ['GET'])]
    public function show(PurchaseOrders $order): Response
    {
        return $this->render('crud/show.html.twig', [
            'title' => 'Commande '.$order->getOrderNumber(),
            'edit_path' => 'app_purchase_orders_edit',
            'back_path' => 'app_purchase_orders_index',
            'entity' => $order,
            'fields' => [
                'Numero' => $order->getOrderNumber(),
                'Fournisseur' => $order->getSupplier()?->getName() ?: '-',
                'Commande par' => $order->getOrderedBy()?->getFullName() ?: '-',
                'Statut' => $order->getStatusLabel(),
                'Date commande' => $order->getOrderDate()?->format('d/m/Y'),
                'Date attendue' => $order->getExpectedDate()?->format('d/m/Y') ?: '-',
                'Total' => $order->getTotalAmount() ?: '-',
                'Lignes' => (string) $order->getItems()->count(),
            ],
        ]);
    }

    #[Route('/{id}/edit', name: 'app_purchase_orders_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PurchaseOrders $order, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PurchaseOrdersType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Commande modifiee.');
            return $this->redirectToRoute('app_purchase_orders_index');
        }
        return $this->render('crud/form.html.twig', ['title' => 'Modifier commande', 'form' => $form, 'back_path' => 'app_purchase_orders_index']);
    }
}
