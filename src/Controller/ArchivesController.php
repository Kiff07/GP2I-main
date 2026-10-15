<?php

namespace App\Controller;

use App\Entity\Archives;
use App\Form\ArchivesType;
use App\Repository\ArchivesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/archives')]
class ArchivesController extends AbstractController
{
    #[Route('/', name: 'app_archives_index', methods: ['GET'])]
    public function index(ArchivesRepository $repository): Response
    {
        return $this->render('crud/index.html.twig', [
            'title' => 'Archives',
            'new_path' => 'app_archives_new',
            'headers' => ['Entité', 'ID', 'Action', 'Créé par', 'Date'],
            'rows' => array_map(fn (Archives $archive) => [
                'entity' => $archive,
                'show_path' => 'app_archives_show',
                'edit_path' => 'app_archives_edit',
                'values' => [$archive->getEntity(), $archive->getEntityId(), $archive->getAction(), $archive->getCreatedBy()?->getFullName() ?: '-', $archive->getCreatedAt()?->format('d/m/Y')],
            ], $repository->findBy([], ['id' => 'DESC'])),
        ]);
    }

    #[Route('/new', name: 'app_archives_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $archive = new Archives();
        $form = $this->createForm(ArchivesType::class, $archive);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $archive->setData($this->decodeJsonData((string) $form->get('data')->getData()));
            $em->persist($archive);
            $em->flush();
            $this->addFlash('success', 'Archive créée.');
            return $this->redirectToRoute('app_archives_index');
        }
        return $this->render('crud/form.html.twig', ['title' => 'Nouvelle archive', 'form' => $form, 'back_path' => 'app_archives_index']);
    }

    #[Route('/{id}', name: 'app_archives_show', methods: ['GET'])]
    public function show(Archives $archive): Response
    {
        return $this->render('crud/show.html.twig', ['title' => (string) $archive, 'edit_path' => 'app_archives_edit', 'back_path' => 'app_archives_index', 'delete_path' => 'app_archives_delete', 'entity' => $archive, 'fields' => ['Entité' => $archive->getEntity(), 'ID entite' => $archive->getEntityId(), 'Action' => $archive->getAction(), 'Créé par' => $archive->getCreatedBy()?->getFullName() ?: '-', 'Date' => $archive->getCreatedAt()?->format('d/m/Y H:i'), 'Données' => json_encode($archive->getData(), JSON_PRETTY_PRINT) ?: '-']]);
    }

    #[Route('/{id}/edit', name: 'app_archives_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function edit(Request $request, Archives $archive, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ArchivesType::class, $archive);
        $form->get('data')->setData($archive->getData() ? json_encode($archive->getData(), JSON_PRETTY_PRINT) : null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $archive->setData($this->decodeJsonData((string) $form->get('data')->getData()));
            $em->flush();
            $this->addFlash('success', 'Archive modifiée.');
            return $this->redirectToRoute('app_archives_index');
        }
        return $this->render('crud/form.html.twig', ['title' => 'Modifier archive', 'form' => $form, 'back_path' => 'app_archives_index']);
    }

    private function decodeJsonData(string $data): ?array
    {
        if (trim($data) === '') {
            return null;
        }

        $decoded = json_decode($data, true);

        return is_array($decoded) ? $decoded : ['raw' => $data];
    }

    #[Route('/{id}', name: 'app_archives_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function delete(Request $request, Archives $archive, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$archive->getId(), (string) $request->request->get('_token'))) {
            $em->remove($archive);
            $em->flush();
            $this->addFlash('success', 'Archive supprimée.');
        }

        return $this->redirectToRoute('app_archives_index', [], Response::HTTP_SEE_OTHER);
    }
}
