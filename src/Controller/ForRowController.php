<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ForRowController extends AbstractController
{
    #[Route('/for/row', name: 'app_for_row')]
    public function index(): Response
    {
        return $this->render('games/four/four.html.twig', [
            'controller_name' => 'ForRowController',
        ]);
    }
}
