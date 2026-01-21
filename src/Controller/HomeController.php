<?php // PHP-Skript beginnt hier
// Definiert den Namespace für diesen Controller
namespace App\Controller;

// Importiert die Station-Entität
use App\Entity\Station;
// Importiert die User-Entität
use App\Entity\User;
// Importiert den Tankerkoenig-Service für API-Interaktionen
use App\Service\TankerkoenigService;
// Importiert den EntityManagerInterface für Datenbankoperationen
use Doctrine\ORM\EntityManagerInterface;
// Importiert den Basis-Controller von Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// Importiert die Request-Klasse für HTTP-Anfragen
use Symfony\Component\HttpFoundation\Request;
// Importiert die Response-Klasse für HTTP-Antworten
use Symfony\Component\HttpFoundation\Response;
// Importiert das Route-Attribut für das Routing
use Symfony\Component\Routing\Attribute\Route;
// Importiert das ParameterBagInterface für den Zugriff auf Konfigurationsparameter
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
// Importiert diverse HttpClient-Exceptions für das Fehlerhandling
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

// Die HomeController-Klasse, die die Hauptseite verwaltet
final class HomeController extends AbstractController
{
    // Konstruktor zur Initialisierung der Abhängigkeiten (Dependency Injection)
    public function __construct(
        // Injektion des Tankerkoenig-Services
        private readonly TankerkoenigService $tankerkoenigService,
        // Injektion des Entity Managers
        private readonly EntityManagerInterface $entityManager,
        // Injektion des Parameter Bags
        private readonly ParameterBagInterface $parameterBag
    ) { // Ende der Parameterliste
    } // Ende des Konstruktors

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    // Definiert die Route für die Startseite
    #[Route('/', name: 'app_home')]
    // Die index-Methode verarbeitet die Anzeige der Tankstellenliste
    public function index(Request $request): Response
    {
        // Holt den aktuell angemeldeten Benutzer
        $user = $this->getUser();
        // Setzt den maximal erlaubten Radius (25km für eingeloggte, 5km für Gäste)
        $maxRadius = $user ? 25 : 5;
        // Holt den gewünschten Radius aus den Query-Parametern (Standard 5)
        $radius = (float) $request->query->get('rad', 5);

        // Stellt sicher, dass der Radius mindestens 1.5km beträgt
        if ($radius < 1.5) $radius = 1.5;
        // Begrenzt den Radius auf das erlaubte Maximum
        if ($radius > $maxRadius) $radius = $maxRadius;

        // Initialisiert die Variable für die aktuell gewählte Adresse
        $currentAddress = null;
        // Holt Breitengrad aus den Query-Parametern
        $lat = $request->query->get('lat');
        // Holt Längengrad aus den Query-Parametern
        $lng = $request->query->get('lng');

        // Wenn ein Benutzer eingeloggt ist, prüfe seine gespeicherten Adressen
        if ($user) {
            // Wenn Koordinaten übergeben wurden und der User vom Typ User ist
            if ($lat && $lng && $user instanceof User) {
                // Durchläuft alle gespeicherten Adressen des Benutzers
                foreach ($user->getAddresses() as $address) {
                    // Vergleicht Koordinaten mit geringer Toleranz für Ungenauigkeiten
                    if (abs((float)$address->getLat() - (float)$lat) < 0.0001 && abs((float)$address->getLng() - (float)$lng) < 0.0001) {
                        // Setzt die gefundene Adresse als aktuelle Adresse
                        $currentAddress = $address;
                        // Bricht die Suche ab, da Adresse gefunden wurde
                        break;
                    } // Ende If-Koordinatenvergleich
                } // Ende Foreach-Adressen
            } // Ende If-Koordinaten-Check
        } // Ende If-User-Check

        // Falls kein Benutzer eingeloggt ist, nutze Standard-Koordinaten
        if (!$user) {
            // Lädt Standard-Breitengrad aus der Konfiguration
            $lat = $this->parameterBag->get('nig_lat');
            // Lädt Standard-Längengrad aus der Konfiguration
            $lng = $this->parameterBag->get('nig_lng');
        } // Ende If-Kein-User

        // Falls Koordinaten aus irgendeinem Grund leer sind, nutze Standards
        if (empty($lat) || empty($lng)) {
            // Lädt Standard-Breitengrad
            $lat = $this->parameterBag->get('nig_lat');
            // Lädt Standard-Längengrad
            $lng = $this->parameterBag->get('nig_lng');
        } // Ende If-Empty-Check

        // Holt den gewählten Kraftstofftyp (Standard: diesel)
        $selectedFuel = $request->query->get('fuel', 'diesel');
        // Prüft, ob der gewählte Kraftstoff gültig ist
        if (!in_array($selectedFuel, ['diesel', 'e5', 'e10'])) {
            // Fallback auf Diesel bei ungültiger Eingabe
            $selectedFuel = 'diesel';
        } // Ende If-Kraftstoffvalidierung

        // Holt die IDs der Tankstellen im Umkreis über den Service
        $stationIds = $this->tankerkoenigService->fetchStations($lat, $lng, $radius);

        // Erstellt einen QueryBuilder für die Station-Entität
        $queryBuilder = $this->entityManager->getRepository(Station::class)->createQueryBuilder('s');
        // Konfiguriert die Datenbankabfrage
        $queryBuilder
            // Filtert nach den gefundenen Station-IDs
            ->where('s.uuid IN (:ids)')
            // Bindet das ID-Array an den Parameter
            ->setParameter('ids', $stationIds)
            // Stellt sicher, dass ein Preis für den gewählten Kraftstoff vorliegt
            ->andWhere('s.' . $selectedFuel . ' IS NOT NULL')
            // Sortiert nach Preis aufsteigend
            ->orderBy('s.' . $selectedFuel, 'ASC')
            // Sortiert zusätzlich nach Distanz aufsteigend
            ->addOrderBy('s.distance', 'ASC');

        // Führt die Abfrage aus und speichert das Ergebnis
        $stations = $queryBuilder
            // Erzeugt die Query
            ->getQuery()
            // Holt die Resultate als Array von Objekten
            ->getResult();

        // Rendert das Twig-Template und übergibt die benötigten Daten
        return $this->render('home/index.html.twig', [
            // Liste der gefundenen Tankstellen
            'stations' => $stations,
            // Status, ob die API-Anfrage fehlgeschlagen ist
            'api_error' => $this->tankerkoenigService->lastRequestFailed(),
            // Aktuell genutzter Suchradius
            'current_radius' => $radius,
            // Aktuell genutzter Breitengrad
            'current_lat' => $lat,
            // Aktuell genutzter Längengrad
            'current_lng' => $lng,
            // Aktuell gewählter Kraftstoff
            'selected_fuel' => $selectedFuel,
            // Aktuell gewählte Adresse des Users (falls vorhanden)
            'current_address' => $currentAddress,
            // Liste aller gespeicherten Adressen des Users
            'user_addresses' => $user instanceof User ? $user->getAddresses() : [],
            // Standard-Adressdaten für die Anzeige
            'default_address' => [
                // Name der Standard-Adresse
                'name' => $this->parameterBag->get('nig_name'),
                // Straße der Standard-Adresse
                'street' => $this->parameterBag->get('nig_street'),
                // PLZ der Standard-Adresse
                'postcode' => $this->parameterBag->get('nig_postcode'),
                // Stadt der Standard-Adresse
                'city' => $this->parameterBag->get('nig_city'),
            ], // Ende default_address Array
            // Koordinaten der Standard-Adresse
            'default_address_coords' => [
                // Breitengrad
                'lat' => $this->parameterBag->get('nig_lat'),
                // Längengrad
                'lng' => $this->parameterBag->get('nig_lng'),
            ], // Ende default_address_coords Array
        ]); // Ende des render-Aufrufs
    } // Ende der index-Methode

    // Definiert die Route für die Detailansicht einer Tankstelle
    #[Route('/station/{uuid}', name: 'app_station_detail')]
    // Die detail-Methode zeigt Einzelheiten zu einer Tankstelle an
    public function detail(string $uuid): Response
    {
        // Holt die Details der Station über den Service
        $station = $this->tankerkoenigService->fetchStationDetail($uuid);

        // Prüft, ob die Station gefunden wurde
        if (!$station) {
            // Wirft eine 404-Exception, wenn die Station nicht existiert
            throw $this->createNotFoundException('Station not found');
        } // Ende If-Check

        // Rendert das Detail-Template
        return $this->render('home/detail.html.twig', [
            // Übergibt das Station-Objekt an das Template
            'station' => $station,
        ]); // Ende des render-Aufrufs
    } // Ende der detail-Methode
} // Ende der HomeController-Klasse
