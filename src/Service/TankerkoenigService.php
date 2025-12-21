<?php

namespace App\Service;

use App\Entity\Price;
use App\Entity\Station;
use App\Entity\StationDetail;
use App\Entity\OpeningTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class TankerkoenigService
{
    private const API_KEY = 'a2ad9604-4f9f-0021-5fb3-e8e150cb670b';
    private bool $lastRequestFailed = false;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function lastRequestFailed(): bool
    {
        return $this->lastRequestFailed;
    }

    public function fetchStations(string $lat, string $lng, float $radius): array
    {
        $this->lastRequestFailed = false;
        try {
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

            if ($response->getStatusCode() !== 200) {
                $this->logger->error('Tankerkoenig API returned status code ' . $response->getStatusCode());
                $this->lastRequestFailed = true;
                return [];
            }

            $data = $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Tankerkoenig API error: ' . $e->getMessage());
            $this->lastRequestFailed = true;
            return [];
        }
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

            // Distanz berechnen
            $distance = $this->calculateDistance($lat, $lng, $station->getLat(), $station->getLng());
            $station->setDistance((string)round($distance, 2));

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

    private function calculateDistance(string $lat1, string $lng1, string $lat2, string $lng2): float
    {
        $earthRadius = 6371; // km

        $latDelta = deg2rad((float)$lat2 - (float)$lat1);
        $lonDelta = deg2rad((float)$lng2 - (float)$lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad((float)$lat1)) * cos(deg2rad((float)$lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function fetchPrices(array $stationIds, array $stationsMap): void
    {
        try {
            $pricesResponse = $this->httpClient->request(
                'GET',
                sprintf(
                    'https://creativecommons.tankerkoenig.de/json/prices.php?ids=%s&apikey=%s',
                    implode(',', $stationIds),
                    self::API_KEY
                )
            );

            if ($pricesResponse->getStatusCode() !== 200) {
                $this->logger->error('Tankerkoenig Prices API returned status code ' . $pricesResponse->getStatusCode());
                return;
            }

            $pricesData = $pricesResponse->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Tankerkoenig Prices API error: ' . $e->getMessage());
            return;
        }

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

        try {
            $response = $this->httpClient->request(
                'GET',
                sprintf(
                    'https://creativecommons.tankerkoenig.de/json/detail.php?id=%s&apikey=%s',
                    $uuid,
                    self::API_KEY
                )
            );

            if ($response->getStatusCode() !== 200) {
                $this->logger->error('Tankerkoenig Detail API returned status code ' . $response->getStatusCode());
                return $station;
            }

            $data = $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Tankerkoenig Detail API error: ' . $e->getMessage());
            return $station;
        }

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
