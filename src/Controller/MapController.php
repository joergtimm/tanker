<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Marker;

final class MapController extends AbstractController
{
    #[Route('/map', name: 'app_map')]
    public function index(): Response
    {
        $map = (new Map())
            ->center(new Point(53.611, 8.818))
            ->zoom(12)
            ->addMarker(new Marker(new Point(53.611, 8.818), 'Mein Ziel'));

        return $this->render('map/index.html.twig', [
            'map' => $map,
        ]);
    }
}
