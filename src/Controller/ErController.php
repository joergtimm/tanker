<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ErController extends AbstractController
{
    #[Route('/er', name: 'app_er')]
    public function index(): Response
    {
        return $this->render('er/index.html.twig', [
            'controller_name' => 'ErController',
        ]);
    }
}
