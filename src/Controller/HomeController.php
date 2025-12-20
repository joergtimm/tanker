<?php

namespace App\Controller;

use App\Entity\Station;
use App\Service\TankerkoenigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly TankerkoenigService $tankerkoenigService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $radius = (float) $request->query->get('rad', 10);
        if ($radius < 1.5) $radius = 1.5;
        if ($radius > 25) $radius = 25;

        $lat = $request->query->get('lat', '53.611');
        $lng = $request->query->get('lng', '8.818');

        $selectedFuels = $request->query->all('fuels');
        if (empty($selectedFuels)) {
            $selectedFuels = ['diesel', 'e5', 'e10'];
        }

        $this->tankerkoenigService->fetchStations($lat, $lng, $radius);

        $queryBuilder = $this->entityManager->getRepository(Station::class)->createQueryBuilder('s');
        $queryBuilder
            ->where('s.distance <= :radius')
            ->setParameter('radius', $radius);

        $orX = $queryBuilder->expr()->orX();
        if (in_array('diesel', $selectedFuels)) {
            $orX->add('s.diesel IS NOT NULL');
        }
        if (in_array('e5', $selectedFuels)) {
            $orX->add('s.e5 IS NOT NULL');
        }
        if (in_array('e10', $selectedFuels)) {
            $orX->add('s.e10 IS NOT NULL');
        }

        if ($orX->count() > 0) {
            $queryBuilder->andWhere($orX);
        }

        $stations = $queryBuilder
            ->orderBy('s.distance', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('home/index.html.twig', [
            'stations' => $stations,
            'current_radius' => $radius,
            'current_lat' => $lat,
            'current_lng' => $lng,
            'selected_fuels' => $selectedFuels,
        ]);
    }

    #[Route('/station/{uuid}', name: 'app_station_detail')]
    public function detail(string $uuid): Response
    {
        $station = $this->tankerkoenigService->fetchStationDetail($uuid);

        if (!$station) {
            throw $this->createNotFoundException('Station not found');
        }

        return $this->render('home/detail.html.twig', [
            'station' => $station,
        ]);
    }
}
