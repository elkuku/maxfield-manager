<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends BaseController
{
    #[Route('/', name: 'default', methods: ['GET'])]
    public function index(): Response {
        return $this->render(
            'default/index.html.twig',
            [
                'maxfields' => $this->getUser()?->getMaxfields(),
            ]
        );
    }
}
