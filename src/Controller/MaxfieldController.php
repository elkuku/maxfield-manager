<?php

namespace App\Controller;

use App\Entity\Maxfield;
use App\Form\MaxfieldZipType;
use App\Service\FileUploader;
use App\Service\GpxHelper;
use App\Service\MaxfieldHelper;
use App\Service\MaxfieldParser;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/maxfield')]
#[IsGranted('ROLE_USER')]
class MaxfieldController extends BaseController
{
    #[Route('/', name: 'maxfield', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        MaxfieldHelper $maxfieldHelper,
        GpxHelper $gpxHelper,
    ): Response {
        $form = $this->createForm(MaxfieldZipType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $zipFile */
            $zipFile = $form->get('zipfile')->getData();

            if ($zipFile) {
                $uploadPath = $fileUploader->upload($zipFile);
                $parser = new MaxfieldParser($uploadPath);

                $parts = explode(DIRECTORY_SEPARATOR, $uploadPath);

                $name = end($parts);

                $gpx = $gpxHelper->getRouteTrackGpx($parser);

                $maxfield = (new Maxfield())
                    ->setName($name)
                    ->setGpx($gpx)
                    ->setOwner($this->getUser());

                $entityManager->persist($maxfield);
                $entityManager->flush();

                $this->addFlash('success', 'File has been uploaded');
            }
        }

        return $this->renderForm('maxfield/index.html.twig', [
            'form' => $form,
            'maxfields' => $this->getUser()?->getMaxfields(),
        ]);
    }

    #[Route('/show/{id}', name: 'maxfield_show', methods: ['GET'])]
    public function show(Maxfield $maxfield)
    {
        return $this->render(
            'maxfield/show.html.twig',
            [
                'maxfield' => $maxfield,
                'gpx' => str_replace(["\r\n", "\n", "'"], '', $maxfield->getGpx()),
            ]
        );
    }
}
