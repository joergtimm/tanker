<?php // PHP-Skript beginnt hier
// Definiert den Namespace für diesen Controller
namespace App\Controller;

// Importiert die Address-Entität
use App\Entity\Address;
// Importiert den AddressType für das Formular
use App\Form\AddressType;
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
// Importiert das IsGranted-Attribut für die Zugriffskontrolle
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Definiert die Basis-Route für alle Methoden in dieser Klasse
#[Route('/profile')]
// Beschränkt den Zugriff auf Benutzer mit der Rolle ROLE_USER
#[IsGranted('ROLE_USER')]
// Die ProfileController-Klasse, die vom AbstractController erbt
class ProfileController extends AbstractController
{
    // Definiert die Route für die Profil-Hauptseite
    #[Route('', name: 'app_profile')]
    // Die index-Methode gibt eine Response zurück
    public function index(): Response
    {
        // Rendert das Twig-Template und übergibt den aktuellen Benutzer
        return $this->render('profile/index.html.twig', [
            // Holt den aktuell angemeldeten Benutzer
            'user' => $this->getUser(),
        ]); // Ende des render-Aufrufs
    } // Ende der index-Methode

    // Definiert die Route zum Erstellen einer neuen Adresse
    #[Route('/address/new', name: 'app_address_new')]
    // Die newAddress-Methode nimmt Request und EntityManager entgegen
    public function newAddress(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Erstellt eine neue Instanz der Address-Entität
        $address = new Address();
        // Setzt den aktuellen Benutzer als Besitzer der Adresse
        $address->setUser($this->getUser());

        // Erstellt das Formular basierend auf AddressType
        $form = $this->createForm(AddressType::class, $address);
        // Verarbeitet die Daten aus dem aktuellen Request
        $form->handleRequest($request);

        // Prüft, ob das Formular abgesendet wurde und valide ist
        if ($form->isSubmitted() && $form->isValid()) {
            // Bereitet die neue Adresse zum Speichern vor
            $entityManager->persist($address);
            // Schreibt die Änderungen in die Datenbank
            $entityManager->flush();

            // Leitet den Benutzer zurück zur Profilseite weiter
            return $this->redirectToRoute('app_profile');
        } // Ende der if-Bedingung

        // Rendert das Formular-Template, falls nicht abgesendet oder ungültig
        return $this->render('profile/address_new.html.twig', [
            // Übergibt die View des Formulars an das Template
            'form' => $form,
        ]); // Ende des render-Aufrufs
    } // Ende der newAddress-Methode

    // Definiert die Route zum Löschen einer Adresse (nur POST-Anfragen)
    #[Route('/address/{id}/edit', name: 'app_address_edit')]
    public function editAddress(Address $address, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Prüft, ob die Adresse tatsächlich dem angemeldeten Benutzer gehört
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AddressType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/address_edit.html.twig', [
            'form' => $form,
            'address' => $address,
        ]);
    }

    // Definiert die Route zum Löschen einer Adresse (nur POST-Anfragen)
    #[Route('/address/{id}/delete', name: 'app_address_delete', methods: ['POST'])]
    // Die deleteAddress-Methode nutzt ParamConverter für die Address-Entität
    public function deleteAddress(Address $address, EntityManagerInterface $entityManager): Response
    {
        // Prüft, ob die Adresse tatsächlich dem angemeldeten Benutzer gehört
        if ($address->getUser() !== $this->getUser()) {
            // Wirft eine Exception, wenn der Zugriff verweigert wird
            throw $this->createAccessDeniedException();
        } // Ende der Sicherheitsprüfung

        // Markiert die Adresse zum Löschen im EntityManager
        $entityManager->remove($address);
        // Führt den Löschvorgang in der Datenbank aus
        $entityManager->flush();

        // Leitet den Benutzer zurück zur Profilseite weiter
        return $this->redirectToRoute('app_profile');
    } // Ende der deleteAddress-Methode
} // Ende der ProfileController-Klasse
