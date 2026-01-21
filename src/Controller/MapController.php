<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Map\Circle;
use Symfony\UX\Map\InfoWindow;
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
            ->addMarker(new Marker(
                position: new Point(53.611, 8.818),
                title: 'Nig Bedekesa',
                infoWindow: new InfoWindow(
                    headerContent: '<b>Nig Bedekesa</b>',
                    content: 'Das Niedersächsische Internatsgymnasium Bad Bederkesa liegt als landeseigene Schule zentral im Landkreis Cuxhaven und führt als weiterführende Schule nach der Grundschule zum Abitur.'
                ),
                extra: [
                    'type' => 'Park',
                    'icon_mask_url' => 'https://maps.gstatic.com/mapfiles/place_api/icons/v2/tree_pinlet.svg',
                ],
            ));

        $map->addCircle(new Circle(
            center: new Point(53.611, 8.818),
            radius: 5_000, // 5km
            infoWindow: new InfoWindow(
                content: 'A 5km radius circle centered on Paris',
            ),
        ));


        return $this->render('map/index.html.twig', [
            'map' => $map,
        ]);
    }
}
