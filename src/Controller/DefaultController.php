<?php

namespace App\Controller;

use Elkuku\MaxfieldParser\MaxfieldParser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends BaseController
{
    #[Route('/', name: 'default', methods: ['GET'])]
    public function index(
        string $projectDir,
    ): Response {
        $maxfields = $this->getUser()?->getMaxfields();
        return $this->render(
            'default/index.html.twig',
            [
                'maxfields' => $this->getUser()?->getMaxfields(),
            ]
        );
    }
}
