<?php // PHP-Skript beginnt hier
// Definiert den Namespace für diesen Service
namespace App\Service;

// Importiert die Price-Entität
use App\Entity\Price;
// Importiert die Station-Entität
use App\Entity\Station;
// Importiert die StationDetail-Entität
use App\Entity\StationDetail;
// Importiert die OpeningTime-Entität
use App\Entity\OpeningTime;
// Importiert den EntityManagerInterface für Datenbankoperationen
use Doctrine\ORM\EntityManagerInterface;
// Importiert das HttpClientInterface für API-Anfragen
use Symfony\Contracts\HttpClient\HttpClientInterface;
// Importiert das LoggerInterface für das Logging
use Psr\Log\LoggerInterface;

// Die TankerkoenigService-Klasse zur Kommunikation mit der API
class TankerkoenigService
{
    // Der API-Key für Tankerkönig (Konstante)
    private const API_KEY = 'a2ad9604-4f9f-0021-5fb3-e8e150cb670b';
    // Status-Variable, ob die letzte Anfrage fehlgeschlagen ist
    private bool $lastRequestFailed = false;

    // Konstruktor zur Initialisierung der Abhängigkeiten
    public function __construct(
        // Injektion des HTTP-Clients
        private readonly HttpClientInterface $httpClient,
        // Injektion des Entity Managers
        private readonly EntityManagerInterface $entityManager,
        // Injektion des Loggers
        private readonly LoggerInterface $logger
    ) { // Ende der Parameterliste
    } // Ende des Konstruktors

    // Gibt zurück, ob die letzte Anfrage fehlgeschlagen ist
    public function lastRequestFailed(): bool
    {
        // Rückgabe des booleschen Wertes
        return $this->lastRequestFailed;
    } // Ende der Methode

    // Holt Tankstellen basierend auf Koordinaten und Radius
    public function fetchStations(string $lat, string $lng, float $radius): array
    {
        // Setzt den Fehlerstatus initial auf false
        $this->lastRequestFailed = false;
        try { // Versuche API-Anfrage
            // Sendet eine GET-Anfrage an die Tankerkönig-API
            $response = $this->httpClient->request(
                'GET',
                // Formatiert die URL mit den Parametern
                sprintf(
                    'https://creativecommons.tankerkoenig.de/json/list.php?lat=%s&lng=%s&rad=%s&sort=dist&type=all&apikey=%s',
                    $lat,
                    $lng,
                    $radius,
                    self::API_KEY
                )
            );

            // Prüft, ob der HTTP-Statuscode nicht 200 (OK) ist
            if ($response->getStatusCode() !== 200) {
                // Protokolliert den Fehler im Log
                $this->logger->error('Tankerkoenig API returned status code ' . $response->getStatusCode());
                // Setzt Fehlerstatus auf true
                $this->lastRequestFailed = true;
                // Gibt ein leeres Array zurück
                return [];
            } // Ende If-Statuscode

            // Konvertiert die JSON-Antwort in ein Array
            $data = $response->toArray();
        } catch (\Exception $e) { // Fängt Exceptions ab
            // Protokolliert die Fehlermeldung
            $this->logger->error('Tankerkoenig API error: ' . $e->getMessage());
            // Setzt Fehlerstatus auf true
            $this->lastRequestFailed = true;
            // Gibt ein leeres Array zurück
            return [];
        } // Ende Try-Catch
        // Extrahiert die Tankstellen-Daten oder nutzt ein leeres Array
        $stationsData = $data['stations'] ?? [];

        // Initialisiert Arrays für IDs und Mapping
        $stationIds = [];
        $stationsMap = [];

        // Iteriert über alle erhaltenen Tankstellen-Daten
        foreach ($stationsData as $sData) {
            // Sucht existierende Station in DB oder erstellt eine neue
            $station = $this->entityManager->getRepository(Station::class)->findOneBy(['uuid' => $sData['id']]) ?? new Station();
            // Setzt die UUID der Station
            $station->setUuid($sData['id']);
            // Setzt den Namen der Station
            $station->setName($sData['name']);
            // Setzt die Marke (falls vorhanden)
            $station->setBrand($sData['brand'] ?? null);
            // Setzt die Straße (falls vorhanden)
            $station->setStreet($sData['street'] ?? null);
            // Setzt die Hausnummer (falls vorhanden)
            $station->setHouseNumber($sData['houseNumber'] ?? null);
            // Setzt die Postleitzahl (falls vorhanden)
            $station->setPostCode($sData['postCode'] ?? null);
            // Setzt den Ort (falls vorhanden)
            $station->setPlace($sData['place'] ?? null);
            // Setzt den Breitengrad
            $station->setLat((string)($sData['lat'] ?? null));
            // Setzt den Längengrad
            $station->setLng((string)($sData['lng'] ?? null));

            // Berechnet die Distanz zwischen Suchpunkt und Station
            $distance = $this->calculateDistance($lat, $lng, $station->getLat(), $station->getLng());
            // Setzt die gerundete Distanz in der Station-Entität
            $station->setDistance((string)round($distance, 2));

            // Setzt den Status, ob die Station geöffnet ist
            $station->setIsOpen((bool)($sData['isOpen'] ?? false));

            // Bereitet die Station zum Speichern vor
            $this->entityManager->persist($station);
            // Fügt die ID zum ID-Array hinzu
            $stationIds[] = $sData['id'];
            // Speichert Station im Mapping-Array für späteren Zugriff
            $stationsMap[$sData['id']] = $station;
        } // Ende Foreach-Stationen

        // Wenn Stationen gefunden wurden, hole auch deren Preise
        if (!empty($stationIds)) {
            // Ruft die Methode zum Abrufen der Preise auf
            $this->fetchPrices($stationIds, $stationsMap);
        } // Ende If-IDs

        // Schreibt alle Änderungen (Stationen & Preise) in die Datenbank
        $this->entityManager->flush();

        // Gibt die Liste der gefundenen IDs zurück
        return $stationIds;
    } // Ende fetchStations-Methode

    // Berechnet die Distanz zwischen zwei Koordinaten (Haversine-Formel)
    private function calculateDistance(string $lat1, string $lng1, string $lat2, string $lng2): float
    {
        // Erdradius in Kilometern
        $earthRadius = 6371; // km

        // Berechnet die Differenzen in Radiant
        $latDelta = deg2rad((float)$lat2 - (float)$lat1);
        $lonDelta = deg2rad((float)$lng2 - (float)$lng1);

        // Anwendung der Haversine-Formel
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad((float)$lat1)) * cos(deg2rad((float)$lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        // Berechnet den Mittelpunktswinkel
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Rückgabe der Distanz in km
        return $earthRadius * $c;
    } // Ende calculateDistance-Methode

    // Ruft aktuelle Preise für eine Liste von Tankstellen-IDs ab
    private function fetchPrices(array $stationIds, array $stationsMap): void
    {
        try { // Versuche API-Anfrage für Preise
            // Sendet GET-Anfrage für Preis-Details
            $pricesResponse = $this->httpClient->request(
                'GET',
                // Formatiert die URL mit kommaseparierten IDs
                sprintf(
                    'https://creativecommons.tankerkoenig.de/json/prices.php?ids=%s&apikey=%s',
                    implode(',', $stationIds),
                    self::API_KEY
                )
            );

            // Prüft den Statuscode
            if ($pricesResponse->getStatusCode() !== 200) {
                // Protokolliert Fehler
                $this->logger->error('Tankerkoenig Prices API returned status code ' . $pricesResponse->getStatusCode());
                // Beendet die Methode vorzeitig
                return;
            } // Ende If-Statuscode

            // Konvertiert JSON-Antwort in Array
            $pricesData = $pricesResponse->toArray();
        } catch (\Exception $e) { // Fängt Exceptions ab
            // Protokolliert Fehlermeldung
            $this->logger->error('Tankerkoenig Prices API error: ' . $e->getMessage());
            // Beendet die Methode
            return;
        } // Ende Try-Catch

        // Prüft, ob Preise im Daten-Array vorhanden sind
        if (isset($pricesData['prices'])) {
            // Iteriert über die Preise pro Station-UUID
            foreach ($pricesData['prices'] as $uuid => $pData) {
                // Prüft, ob die Station in unserem Map-Array existiert
                if (isset($stationsMap[$uuid])) {
                    // Holt das Station-Objekt aus dem Map-Array
                    $station = $stationsMap[$uuid];

                    // Setzt Diesel-Preis, falls numerisch
                    $station->setDiesel(isset($pData['diesel']) && is_numeric($pData['diesel']) ? (string)$pData['diesel'] : null);
                    // Setzt E5-Preis, falls numerisch
                    $station->setE5(isset($pData['e5']) && is_numeric($pData['e5']) ? (string)$pData['e5'] : null);
                    // Setzt E10-Preis, falls numerisch
                    $station->setE10(isset($pData['e10']) && is_numeric($pData['e10']) ? (string)$pData['e10'] : null);

                    // Erstellt ein neues Preis-Log-Objekt (Historie)
                    $price = new Price();
                    // Verknüpft den Preis mit der Station
                    $price->setStation($station);
                    // Übernimmt Diesel-Preis
                    $price->setDiesel($station->getDiesel());
                    // Übernimmt E5-Preis
                    $price->setE5($station->getE5());
                    // Übernimmt E10-Preis
                    $price->setE10($station->getE10());
                    // Bereitet den Preis-Eintrag zum Speichern vor
                    $this->entityManager->persist($price);
                } // Ende If-Mapping
            } // Ende Foreach-Preise
        } // Ende If-Prices-Set
    } // Ende fetchPrices-Methode

    // Holt detaillierte Informationen zu einer einzelnen Tankstelle
    public function fetchStationDetail(string $uuid): ?Station
    {
        // Sucht die Station in der lokalen Datenbank
        $station = $this->entityManager->getRepository(Station::class)->findOneBy(['uuid' => $uuid]);

        // Wenn Station lokal nicht existiert, gib null zurück
        if (!$station) {
            return null;
        } // Ende If-Nicht-Gefunden

        try { // Versuche API-Anfrage für Details
            // Sendet GET-Anfrage für Detail-Informationen
            $response = $this->httpClient->request(
                'GET',
                // Formatiert URL mit UUID
                sprintf(
                    'https://creativecommons.tankerkoenig.de/json/detail.php?id=%s&apikey=%s',
                    $uuid,
                    self::API_KEY
                )
            );

            // Prüft Statuscode
            if ($response->getStatusCode() !== 200) {
                // Protokolliert Fehler
                $this->logger->error('Tankerkoenig Detail API returned status code ' . $response->getStatusCode());
                // Gibt die (unvollständige) lokale Station zurück
                return $station;
            } // Ende If-Statuscode

            // Konvertiert JSON-Antwort in Array
            $data = $response->toArray();
        } catch (\Exception $e) { // Fängt Exceptions
            // Protokolliert Fehler
            $this->logger->error('Tankerkoenig Detail API error: ' . $e->getMessage());
            // Gibt die lokale Station zurück
            return $station;
        } // Ende Try-Catch

        // Prüft, ob 'station' Key in den Daten vorhanden ist
        if (!isset($data['station'])) {
            // Gibt die lokale Station zurück
            return $station;
        } // Ende If-No-Data

        // Extrahiert die Detaildaten der Station
        $sData = $data['station'];

        // Aktualisiert Basis-Daten der Station (falls sich etwas geändert hat)
        $station->setName($sData['name']);
        // Setzt Marke
        $station->setBrand($sData['brand'] ?? null);
        // Setzt Straße
        $station->setStreet($sData['street'] ?? null);
        // Setzt Hausnummer
        $station->setHouseNumber($sData['houseNumber'] ?? null);
        // Setzt PLZ
        $station->setPostCode($sData['postCode'] ?? null);
        // Setzt Ort
        $station->setPlace($sData['place'] ?? null);
        // Setzt Breitengrad
        $station->setLat((string)($sData['lat'] ?? null));
        // Setzt Längengrad
        $station->setLng((string)($sData['lng'] ?? null));
        // Setzt Öffnungsstatus
        $station->setIsOpen((bool)($sData['isOpen'] ?? false));

        // Holt existierende Details oder erstellt neues Objekt
        $detail = $station->getStationDetail() ?: new StationDetail();
        // Verknüpft Detail mit Station
        $detail->setStation($station);
        $station->setStationDetail($detail);
        // Setzt rohe Öffnungszeiten-Daten
        $detail->setOpeningTimes($sData['openingTimes'] ?? []);
        // Setzt Overrides
        $detail->setOverrides($sData['overrides'] ?? []);
        // Setzt Flag für 24h-Öffnung
        $detail->setWholeDay($sData['wholeDay'] ?? false);
        // Setzt den Bundesland-Status
        $detail->setState($sData['state'] ?? null);

        // Debug-Kommentar (auskommentiert): Speichern der Daten in Datei
        // file_put_contents('debug_detail.json', json_encode($sData, JSON_PRETTY_PRINT));

        // Aktualisiert die OpeningTime-Objekte (löscht alte Einträge)
        foreach ($detail->getOpeningTimeObjects() as $oldOpeningTime) {
            // Markiert alte Öffnungszeit zum Löschen
            $this->entityManager->remove($oldOpeningTime);
        } // Ende Lösch-Schleife
        // Leert die Collection im Detail-Objekt
        $detail->getOpeningTimeObjects()->clear();

        // Prüft, ob neue Öffnungszeiten-Daten vorhanden sind
        if (isset($sData['openingTimes']) && is_array($sData['openingTimes'])) {
            // Iteriert über neue Öffnungszeiten
            foreach ($sData['openingTimes'] as $otData) {
                // Erstellt neues OpeningTime-Objekt
                $openingTime = new OpeningTime();
                // Setzt Beschreibungstext
                $openingTime->setText($otData['text']);
                // Setzt Startzeit
                $openingTime->setStart($otData['start']);
                // Setzt Endzeit
                $openingTime->setEnd($otData['end']);
                // Fügt Objekt zum Detail hinzu
                $detail->addOpeningTimeObject($openingTime);
            } // Ende Foreach-Öffnungszeiten
        } // Ende If-OpeningTimes

        // Bereitet Detail-Objekt zum Speichern vor
        $this->entityManager->persist($detail);
        // Schreibt alle Änderungen in die Datenbank
        $this->entityManager->flush();
        $this->entityManager->refresh($station);

        return $station;
    } // Ende fetchStationDetail-Methode
} // Ende der Klasse TankerkoenigService
