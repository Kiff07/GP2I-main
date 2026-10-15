<?php

namespace App\Controller;

use App\Entity\Loans;
use App\Entity\Materials;
use App\Entity\Users;
use App\Form\LoansType;
use App\Repository\LoansRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Emprunts de matériel.
 * Créer un emprunt sort 1 exemplaire du stock ; le retour le réintègre.
 */
#[Route('/loans')]
class LoansController extends AbstractController
{
    #[Route('/', name: 'app_loans_index', methods: ['GET'])]
    public function index(LoansRepository $repository): Response
    {
        return $this->render('loans/index.html.twig', [
            'loans' => $repository->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_loans_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $loan = new Loans();
        $form = $this->createForm(LoansType::class, $loan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $material = $loan->getMaterial();
            if (null === $material || (int) $material->getQuantity() <= 0) {
                $this->addFlash('error', 'Stock insuffisant : aucun exemplaire disponible pour ce matériel.');

                return $this->render('crud/form.html.twig', ['title' => 'Nouvel emprunt', 'form' => $form, 'back_path' => 'app_loans_index']);
            }
            if ($loan->getDueDate() < $loan->getBorrowedAt()) {
                $this->addFlash('error', 'La date de retour prévue doit être après la date d\'emprunt.');

                return $this->render('crud/form.html.twig', ['title' => 'Nouvel emprunt', 'form' => $form, 'back_path' => 'app_loans_index']);
            }

            // Sortie d'un exemplaire du stock.
            $material->setQuantity((int) $material->getQuantity() - 1);
            if (0 === (int) $material->getQuantity() && Materials::STATUS_AVAILABLE === $material->getStatus()) {
                $material->setStatus(Materials::STATUS_BORROWED);
            }
            $material->setUpdatedAt(new \DateTime());

            $user = $this->getUser();
            if ($user instanceof Users) {
                $loan->setPerformedBy($user);
            }

            $em->persist($loan);
            $em->flush();
            $this->addFlash('success', 'Emprunt enregistré, stock mis à jour.');

            return $this->redirectToRoute('app_loans_index');
        }

        return $this->render('crud/form.html.twig', ['title' => 'Nouvel emprunt', 'form' => $form, 'back_path' => 'app_loans_index']);
    }

    #[Route('/{id}', name: 'app_loans_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Loans $loan): Response
    {
        return $this->render('crud/show.html.twig', [
            'title' => (string) $loan,
            'edit_path' => 'app_loans_edit',
            'back_path' => 'app_loans_index',
            'delete_path' => 'app_loans_delete',
            'return_path' => $loan->isReturned() ? null : 'app_loans_return',
            'entity' => $loan,
            'fields' => [
                'Matériel' => $loan->getMaterial()?->getName(),
                'Emprunteur' => $loan->getBorrowerName(),
                'Date d\'emprunt' => $loan->getBorrowedAt()?->format('d/m/Y'),
                'Retour prévu' => $loan->getDueDate()?->format('d/m/Y'),
                'Retourné le' => $loan->getReturnedAt()?->format('d/m/Y') ?: '-',
                'Statut' => $loan->getStatusLabel(),
                'Enregistré par' => $loan->getPerformedBy()?->getFullName() ?: '-',
                'Note' => $loan->getNote() ?: '-',
            ],
        ]);
    }

    #[Route('/{id}/edit', name: 'app_loans_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_MANAGER')]
    public function edit(Request $request, Loans $loan, EntityManagerInterface $em): Response
    {
        // L'édition corrige les informations, sans toucher au stock.
        $form = $this->createForm(LoansType::class, $loan);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Emprunt modifié.');

            return $this->redirectToRoute('app_loans_index');
        }

        return $this->render('crud/form.html.twig', ['title' => 'Modifier l\'emprunt', 'form' => $form, 'back_path' => 'app_loans_index']);
    }

    #[Route('/{id}/return', name: 'app_loans_return', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_MANAGER')]
    public function returnLoan(Request $request, Loans $loan, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('return'.$loan->getId(), (string) $request->request->get('_token')) && !$loan->isReturned()) {
            $loan->setReturnedAt(new \DateTime());

            // Réintégration de l'exemplaire dans le stock.
            $material = $loan->getMaterial();
            if (null !== $material) {
                $material->setQuantity((int) $material->getQuantity() + 1);
                if (Materials::STATUS_BORROWED === $material->getStatus()) {
                    $material->setStatus(Materials::STATUS_AVAILABLE);
                }
                $material->setUpdatedAt(new \DateTime());
            }

            $em->flush();
            $this->addFlash('success', 'Matériel retourné, stock mis à jour.');
        }

        return $this->redirectToRoute('app_loans_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_loans_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_MANAGER')]
    public function delete(Request $request, Loans $loan, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$loan->getId(), (string) $request->request->get('_token'))) {
            // Supprimer un emprunt en cours réintègre l'exemplaire au stock.
            if (!$loan->isReturned() && null !== $loan->getMaterial()) {
                $material = $loan->getMaterial();
                $material->setQuantity((int) $material->getQuantity() + 1);
                if (Materials::STATUS_BORROWED === $material->getStatus()) {
                    $material->setStatus(Materials::STATUS_AVAILABLE);
                }
            }
            $em->remove($loan);
            $em->flush();
            $this->addFlash('success', 'Emprunt supprimé.');
        }

        return $this->redirectToRoute('app_loans_index', [], Response::HTTP_SEE_OTHER);
    }
}
