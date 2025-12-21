<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SchemaController extends AbstractController
{
    #[Route('/schema', name: 'app_schema')]
    public function index(): Response
    {
        return $this->render('schema/index.html.twig');
    }
}
