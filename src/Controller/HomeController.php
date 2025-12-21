<?php

namespace App\Controller;

use App\Entity\Station;
use App\Entity\User;
use App\Service\TankerkoenigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class HomeController extends AbstractController
{

    public function __construct(
        private readonly TankerkoenigService $tankerkoenigService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $parameterBag
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
        $user = $this->getUser();
        $maxRadius = $user ? 25 : 5;
        $radius = (float) $request->query->get('rad', 5);

        if ($radius < 1.5) $radius = 1.5;
        if ($radius > $maxRadius) $radius = $maxRadius;

        $currentAddress = null;
        $lat = $request->query->get('lat');
        $lng = $request->query->get('lng');

        if ($user) {
            if ($lat && $lng && $user instanceof User) {
                foreach ($user->getAddresses() as $address) {
                    if (abs((float)$address->getLat() - (float)$lat) < 0.0001 && abs((float)$address->getLng() - (float)$lng) < 0.0001) {
                        $currentAddress = $address;
                        break;
                    }
                }
            }
        }

        if (!$user) {
            $lat = $this->parameterBag->get('nig_lat');
            $lng = $this->parameterBag->get('nig_lng');
        }

        if (empty($lat) || empty($lng)) {
            $lat = $this->parameterBag->get('nig_lat');
            $lng = $this->parameterBag->get('nig_lng');
        }

        $selectedFuel = $request->query->get('fuel', 'diesel');
        if (!in_array($selectedFuel, ['diesel', 'e5', 'e10'])) {
            $selectedFuel = 'diesel';
        }

        $stationIds = $this->tankerkoenigService->fetchStations($lat, $lng, $radius);

        $queryBuilder = $this->entityManager->getRepository(Station::class)->createQueryBuilder('s');
        $queryBuilder
            ->where('s.uuid IN (:ids)')
            ->setParameter('ids', $stationIds)
            ->andWhere('s.' . $selectedFuel . ' IS NOT NULL')
            ->orderBy('s.' . $selectedFuel, 'ASC')
            ->addOrderBy('s.distance', 'ASC');

        $stations = $queryBuilder
            ->getQuery()
            ->getResult();

        return $this->render('home/index.html.twig', [
            'stations' => $stations,
            'api_error' => $this->tankerkoenigService->lastRequestFailed(),
            'current_radius' => $radius,
            'current_lat' => $lat,
            'current_lng' => $lng,
            'selected_fuel' => $selectedFuel,
            'current_address' => $currentAddress,
            'user_addresses' => $user instanceof User ? $user->getAddresses() : [],
            'default_address' => [
                'name' => $this->parameterBag->get('nig_name'),
                'street' => $this->parameterBag->get('nig_street'),
                'postcode' => $this->parameterBag->get('nig_postcode'),
                'city' => $this->parameterBag->get('nig_city'),
            ],
            'default_address_coords' => [
                'lat' => $this->parameterBag->get('nig_lat'),
                'lng' => $this->parameterBag->get('nig_lng'),
            ],
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
