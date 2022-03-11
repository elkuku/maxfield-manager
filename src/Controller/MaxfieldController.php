<?php

namespace App\Controller;

use App\Entity\Maxfield;
use App\Form\MaxfieldType;
use App\Form\MaxfieldZipType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Elkuku\MaxfieldParser\GpxHelper;
use Elkuku\MaxfieldParser\JsonHelper;
use Elkuku\MaxfieldParser\MaxfieldParser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/maxfield')]
#[IsGranted('ROLE_USER')]
class MaxfieldController extends BaseController
{
    #[Route('/new', name: 'maxfield_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
    ): Response {
        $form = $this->createForm(MaxfieldZipType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $zipFile = $form->get('zipfile')->getData();

            if ($zipFile instanceof UploadedFile) {
                $uploadPath = $fileUploader->upload($zipFile);

                $parts = explode(DIRECTORY_SEPARATOR, $uploadPath);

                $name = end($parts);

                $parser = new MaxfieldParser($uploadPath);

                $maxfieldObject = $parser->parse();

                $aaa = json_encode($maxfieldObject, JSON_THROW_ON_ERROR);

                $gpx = (new GpxHelper())
                    ->getRouteTrackGpx(new MaxfieldParser($uploadPath));

                $json = (new JsonHelper())
                    ->getJsonData(new MaxfieldParser($uploadPath));

                $maxfield = (new Maxfield())
                    ->setName($name)
                    ->setGpx($gpx)
                    ->setJsonData($json)
                    ->setOwner($this->getUser());

                $entityManager->persist($maxfield);
                $entityManager->flush();

                $this->addFlash('success', 'File has been uploaded');

                return $this->redirectToRoute('default');
            }
        }

        return $this->renderForm('maxfield/new.html.twig', [
            'form'      => $form,
            'maxfields' => $this->getUser()?->getMaxfields(),
        ]);
    }

    #[Route('/show/{id}', name: 'maxfield_show', methods: ['GET'])]
    public function show(Maxfield $maxfield): Response
    {
        return $this->render(
            'maxfield/show.html.twig',
            [
                'maxfield' => $maxfield,
                'jsonData' => $maxfield->getJsonData(),
                'gpx'      => str_replace(["\r\n", "\n", "'"],
                    '',
                    (string)$maxfield->getGpx()),
            ]
        );
    }

    #[Route('/edit/{id}', name: 'maxfield_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        Maxfield $maxfield,
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')
            && $maxfield->getOwner() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException('No access for you!');
        }

        $form = $this->createForm(MaxfieldType::class, $maxfield);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $maxfield = $form->getData();
            $entityManager->persist($maxfield);
            $entityManager->flush();

            $this->addFlash('success', 'Maxfield updated!');

            return $this->redirectToRoute('default');
        }

        return $this->renderForm(
            'maxfield/edit.html.twig',
            [
                'form'     => $form,
                'maxfield' => $maxfield,
            ]
        );
    }

    #[Route('/delete/{id}', name: 'maxfield_delete', methods: ['GET'])]
    public function delete(
        EntityManagerInterface $entityManager,
        Maxfield $maxfield,
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')
            && $maxfield->getOwner() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException('No access for you!');
        }

        $entityManager->remove($maxfield);
        $entityManager->flush();

        $this->addFlash('success', 'Maxfield has been removed.');

        return $this->redirectToRoute('default');
    }
}
