<?php

namespace App\Service;

use App\Entity\Price;
use App\Entity\Station;
use App\Entity\StationDetail;
use App\Entity\OpeningTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TankerkoenigService
{
    private const API_KEY = 'a2ad9604-4f9f-0021-5fb3-e8e150cb670b';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function fetchStations(string $lat, string $lng, float $radius): array
    {
        $response = $this->httpClient->request(
            'GET',
            sprintf(
                'https://creativecommons.tankerkoenig.de/json/list.php?lat=%s&lng=%s&rad=%s&sort=dist&type=all&apikey=%s',
                $lat,
                $lng,
                $radius,
                self::API_KEY
            )
        );

        $data = $response->toArray();
        $stationsData = $data['stations'] ?? [];

        $stationIds = [];
        $stationsMap = [];

        foreach ($stationsData as $sData) {
            $station = $this->entityManager->getRepository(Station::class)->findOneBy(['uuid' => $sData['id']]) ?? new Station();
            $station->setUuid($sData['id']);
            $station->setName($sData['name']);
            $station->setBrand($sData['brand'] ?? null);
            $station->setStreet($sData['street'] ?? null);
            $station->setHouseNumber($sData['houseNumber'] ?? null);
            $station->setPostCode($sData['postCode'] ?? null);
            $station->setPlace($sData['place'] ?? null);
            $station->setLat((string)($sData['lat'] ?? null));
            $station->setLng((string)($sData['lng'] ?? null));
            $station->setDistance((string)($sData['dist'] ?? 0));
            $station->setIsOpen((bool)($sData['isOpen'] ?? false));

            $this->entityManager->persist($station);
            $stationIds[] = $sData['id'];
            $stationsMap[$sData['id']] = $station;
        }

        if (!empty($stationIds)) {
            $this->fetchPrices($stationIds, $stationsMap);
        }

        $this->entityManager->flush();

        return $stationIds;
    }

    private function fetchPrices(array $stationIds, array $stationsMap): void
    {
        $pricesResponse = $this->httpClient->request(
            'GET',
            sprintf(
                'https://creativecommons.tankerkoenig.de/json/prices.php?ids=%s&apikey=%s',
                implode(',', $stationIds),
                self::API_KEY
            )
        );
        $pricesData = $pricesResponse->toArray();

        if (isset($pricesData['prices'])) {
            foreach ($pricesData['prices'] as $uuid => $pData) {
                if (isset($stationsMap[$uuid])) {
                    $station = $stationsMap[$uuid];

                    $station->setDiesel(isset($pData['diesel']) && is_numeric($pData['diesel']) ? (string)$pData['diesel'] : null);
                    $station->setE5(isset($pData['e5']) && is_numeric($pData['e5']) ? (string)$pData['e5'] : null);
                    $station->setE10(isset($pData['e10']) && is_numeric($pData['e10']) ? (string)$pData['e10'] : null);

                    $price = new Price();
                    $price->setStation($station);
                    $price->setDiesel($station->getDiesel());
                    $price->setE5($station->getE5());
                    $price->setE10($station->getE10());
                    $this->entityManager->persist($price);
                }
            }
        }
    }

    public function fetchStationDetail(string $uuid): ?Station
    {
        $station = $this->entityManager->getRepository(Station::class)->findOneBy(['uuid' => $uuid]);

        if (!$station) {
            return null;
        }

        $response = $this->httpClient->request(
            'GET',
            sprintf(
                'https://creativecommons.tankerkoenig.de/json/detail.php?id=%s&apikey=%s',
                $uuid,
                self::API_KEY
            )
        );

        $data = $response->toArray();

        if (!isset($data['station'])) {
            return $station;
        }

        $sData = $data['station'];

        // Update station basics if needed
        $station->setName($sData['name']);
        $station->setBrand($sData['brand'] ?? null);
        $station->setStreet($sData['street'] ?? null);
        $station->setHouseNumber($sData['houseNumber'] ?? null);
        $station->setPostCode($sData['postCode'] ?? null);
        $station->setPlace($sData['place'] ?? null);
        $station->setLat((string)($sData['lat'] ?? null));
        $station->setLng((string)($sData['lng'] ?? null));
        $station->setIsOpen((bool)($sData['isOpen'] ?? false));

        $detail = $station->getStationDetail() ?? new StationDetail();
        $detail->setStation($station);
        $detail->setOpeningTimes($sData['openingTimes'] ?? []);
        $detail->setOverrides($sData['overrides'] ?? []);
        $detail->setWholeDay($sData['wholeDay'] ?? false);
        $detail->setState($sData['state'] ?? null);

        // Debug: Log data if needed
        // file_put_contents('debug_detail.json', json_encode($sData, JSON_PRETTY_PRINT));

        // Update opening times
        foreach ($detail->getOpeningTimeObjects() as $oldOpeningTime) {
            $this->entityManager->remove($oldOpeningTime);
        }
        $detail->getOpeningTimeObjects()->clear();

        if (isset($sData['openingTimes']) && is_array($sData['openingTimes'])) {
            foreach ($sData['openingTimes'] as $otData) {
                $openingTime = new OpeningTime();
                $openingTime->setText($otData['text']);
                $openingTime->setStart($otData['start']);
                $openingTime->setEnd($otData['end']);
                $detail->addOpeningTimeObject($openingTime);
            }
        }

        $this->entityManager->persist($detail);
        $this->entityManager->flush();

        return $station;
    }
}
