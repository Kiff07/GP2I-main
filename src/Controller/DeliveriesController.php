<?php

namespace App\Controller;

use App\Entity\Deliveries;
use App\Form\DeliveriesType;
use App\Repository\DeliveriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/deliveries')]
class DeliveriesController extends AbstractController
{
    #[Route('/', name: 'app_deliveries_index', methods: ['GET'])]
    public function index(DeliveriesRepository $repository): Response
    {
        return $this->render('crud/index.html.twig', [
            'title' => 'Livraisons',
            'new_path' => 'app_deliveries_new',
            'headers' => ['Commande', 'Date', 'Recu par', 'Note'],
            'rows' => array_map(fn (Deliveries $delivery) => [
                'entity' => $delivery,
                'show_path' => 'app_deliveries_show',
                'edit_path' => 'app_deliveries_edit',
                'values' => [$delivery->getPurchaseOrder()?->getOrderNumber() ?: '-', $delivery->getDeliveryDate()?->format('d/m/Y'), $delivery->getReceivedBy()?->getFullName() ?: '-', $delivery->getNote() ? mb_strimwidth($delivery->getNote(), 0, 50, '...') : '-'],
            ], $repository->findBy([], ['id' => 'DESC'])),
        ]);
    }

    #[Route('/new', name: 'app_deliveries_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $delivery = new Deliveries();
        $form = $this->createForm(DeliveriesType::class, $delivery);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { $em->persist($delivery); $em->flush(); $this->addFlash('success', 'Livraison creee.'); return $this->redirectToRoute('app_deliveries_index'); }
        return $this->render('crud/form.html.twig', ['title' => 'Nouvelle livraison', 'form' => $form, 'back_path' => 'app_deliveries_index']);
    }

    #[Route('/{id}', name: 'app_deliveries_show', methods: ['GET'])]
    public function show(Deliveries $delivery): Response
    {
        return $this->render('crud/show.html.twig', ['title' => (string) $delivery, 'edit_path' => 'app_deliveries_edit', 'back_path' => 'app_deliveries_index', 'delete_path' => 'app_deliveries_delete', 'entity' => $delivery, 'fields' => ['Commande' => $delivery->getPurchaseOrder()?->getOrderNumber() ?: '-', 'Date' => $delivery->getDeliveryDate()?->format('d/m/Y'), 'Recu par' => $delivery->getReceivedBy()?->getFullName() ?: '-', 'Note' => $delivery->getNote() ?: '-']]);
    }

    #[Route('/{id}/edit', name: 'app_deliveries_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function edit(Request $request, Deliveries $delivery, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DeliveriesType::class, $delivery);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { $em->flush(); $this->addFlash('success', 'Livraison modifiee.'); return $this->redirectToRoute('app_deliveries_index'); }
        return $this->render('crud/form.html.twig', ['title' => 'Modifier livraison', 'form' => $form, 'back_path' => 'app_deliveries_index']);
    }

    #[Route('/{id}', name: 'app_deliveries_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function delete(Request $request, Deliveries $delivery, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$delivery->getId(), (string) $request->request->get('_token'))) {
            $em->remove($delivery);
            $em->flush();
            $this->addFlash('success', 'Livraison supprimee.');
        }

        return $this->redirectToRoute('app_deliveries_index', [], Response::HTTP_SEE_OTHER);
    }
}
