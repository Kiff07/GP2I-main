<?php

namespace App\Controller;

use App\Entity\AssetImages;
use App\Form\AssetImagesType;
use App\Repository\AssetImagesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
            'headers' => ['Matériel', 'Fichier', 'Légende', 'Date'],
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
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->storeUploadedFile($image, $form->get('imageFile')->getData())) {
                $this->addFlash('error', 'Veuillez choisir une photo à envoyer.');

                return $this->render('crud/form.html.twig', ['title' => 'Nouvelle image', 'form' => $form, 'back_path' => 'app_asset_images_index']);
            }
            $em->persist($image);
            $em->flush();
            $this->addFlash('success', 'Image créée.');

            return $this->redirectToRoute('app_asset_images_index');
        }

        return $this->render('crud/form.html.twig', ['title' => 'Nouvelle image', 'form' => $form, 'back_path' => 'app_asset_images_index']);
    }

    #[Route('/{id}', name: 'app_asset_images_show', methods: ['GET'])]
    public function show(AssetImages $image): Response
    {
        return $this->render('crud/show.html.twig', ['title' => (string) $image, 'edit_path' => 'app_asset_images_edit', 'back_path' => 'app_asset_images_index', 'delete_path' => 'app_asset_images_delete', 'entity' => $image, 'fields' => ['Matériel' => $image->getMaterial()?->getName(), 'Fichier' => $image->getFilename(), 'Légende' => $image->getCaption() ?: '-', 'Date' => $image->getCreatedAt()?->format('d/m/Y H:i')]]);
    }

    #[Route('/{id}/edit', name: 'app_asset_images_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function edit(Request $request, AssetImages $image, EntityManagerInterface $em): Response
    {
        $oldFilename = $image->getFilename();
        $form = $this->createForm(AssetImagesType::class, $image);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->storeUploadedFile($image, $form->get('imageFile')->getData())) {
                $this->deletePhysicalFile($oldFilename);
            }
            $em->flush();
            $this->addFlash('success', 'Image modifiée.');

            return $this->redirectToRoute('app_asset_images_index');
        }

        return $this->render('crud/form.html.twig', ['title' => 'Modifier image', 'form' => $form, 'back_path' => 'app_asset_images_index']);
    }

    #[Route('/{id}', name: 'app_asset_images_delete', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function delete(Request $request, AssetImages $image, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), (string) $request->request->get('_token'))) {
            $filename = $image->getFilename();
            $em->remove($image);
            $em->flush();
            $this->deletePhysicalFile($filename);
            $this->addFlash('success', 'Image supprimée.');
        }

        return $this->redirectToRoute('app_asset_images_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Enregistre le fichier envoyé dans public/uploads et renseigne
     * le nom de fichier de l'entité. Retourne false s'il n'y a pas de fichier.
     */
    private function storeUploadedFile(AssetImages $image, mixed $file): bool
    {
        if (!$file instanceof UploadedFile) {
            return false;
        }

        $safeName = bin2hex(random_bytes(10)).'.'.($file->guessExtension() ?? 'jpg');
        $file->move($this->getParameter('kernel.project_dir').'/public/uploads', $safeName);
        $image->setFilename($safeName);

        return true;
    }

    private function deletePhysicalFile(?string $filename): void
    {
        if (!$filename) {
            return;
        }
        $path = $this->getParameter('kernel.project_dir').'/public/uploads/'.$filename;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
