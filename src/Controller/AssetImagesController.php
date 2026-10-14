<?php

namespace App\Controller;

use App\Entity\AssetImages;
use App\Form\AssetImagesType;
use App\Repository\AssetImagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/asset-images')]
class AssetImagesController extends AbstractController
{
    #[Route('/', name: 'app_asset_images_index', methods: ['GET'])]
    public function index(AssetImagesRepository $repository): Response
    {
        return $this->render('crud/index.html.twig', [
            'title' => 'Images',
            'new_path' => 'app_asset_images_new',
            'headers' => ['Materiel', 'Fichier', 'Legende', 'Date'],
            'rows' => array_map(fn (AssetImages $image) => [
                'entity' => $image,
                'show_path' => 'app_asset_images_show',
                'edit_path' => 'app_asset_images_edit',
                'values' => [$image->getMaterial()?->getName(), $image->getFilename(), $image->getCaption() ?: '-', $image->getCreatedAt()?->format('d/m/Y')],
            ], $repository->findBy([], ['id' => 'DESC'])),
        ]);
    }

    #[Route('/new', name: 'app_asset_images_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $image = new AssetImages();
        $form = $this->createForm(AssetImagesType::class, $image);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { $em->persist($image); $em->flush(); $this->addFlash('success', 'Image creee.'); return $this->redirectToRoute('app_asset_images_index'); }
        return $this->render('crud/form.html.twig', ['title' => 'Nouvelle image', 'form' => $form, 'back_path' => 'app_asset_images_index']);
    }

    #[Route('/{id}', name: 'app_asset_images_show', methods: ['GET'])]
    public function show(AssetImages $image): Response
    {
        return $this->render('crud/show.html.twig', ['title' => (string) $image, 'edit_path' => 'app_asset_images_edit', 'back_path' => 'app_asset_images_index', 'delete_path' => 'app_asset_images_delete', 'entity' => $image, 'fields' => ['Materiel' => $image->getMaterial()?->getName(), 'Fichier' => $image->getFilename(), 'Legende' => $image->getCaption() ?: '-', 'Date' => $image->getCreatedAt()?->format('d/m/Y H:i')]]);
    }

    #[Route('/{id}/edit', name: 'app_asset_images_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function edit(Request $request, AssetImages $image, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AssetImagesType::class, $image);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { $em->flush(); $this->addFlash('success', 'Image modifiee.'); return $this->redirectToRoute('app_asset_images_index'); }
        return $this->render('crud/form.html.twig', ['title' => 'Modifier image', 'form' => $form, 'back_path' => 'app_asset_images_index']);
    }

    #[Route('/{id}', name: 'app_asset_images_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function delete(Request $request, AssetImages $image, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), (string) $request->request->get('_token'))) {
            $em->remove($image);
            $em->flush();
            $this->addFlash('success', 'Image supprimee.');
        }

        return $this->redirectToRoute('app_asset_images_index', [], Response::HTTP_SEE_OTHER);
    }
}
