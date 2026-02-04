<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testIndexAsGuest(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tankstellen entdecken.');

        // Check that lat/lng inputs ARE present as hidden for guests
        $this->assertSelectorExists('input#lat[type="hidden"]');
        $this->assertSelectorExists('input#lng[type="hidden"]');

        // Check for the fixed address text
        $this->assertSelectorTextContains('div.mb-8.p-5 div.font-bold', 'Niedersächsisches Internatsgymnasium');
        $this->assertSelectorTextContains('div.mb-8.p-5 div.text-sm', 'Seminarstraße 8, Geestland');

        // Check for login button
        $this->assertSelectorTextContains('a.bg-blue-50', 'Eigene Adressen nutzen');
    }

    public function testIndexAsUser(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $testUser = new \App\Entity\User();
        $testUser->setEmail('test_unique_'.uniqid().'@example.com');
        $testUser->setPassword('password');

        $address = new \App\Entity\Address();
        $address->setName('Test Home');
        $address->setStreet('Main St 1');
        $address->setPostCode('12345');
        $address->setCity('Test City');
        $address->setLat('53.123');
        $address->setLng('8.123');
        $address->setUser($testUser);
        $testUser->addAddress($address);

        $entityManager->persist($testUser);
        $entityManager->persist($address);
        $entityManager->flush();

        $client->loginUser($testUser);
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        // Now lat/lng should be hidden inputs
        $this->assertSelectorExists('input#lat[type="hidden"]');
        $this->assertSelectorExists('input#lng[type="hidden"]');

        // Address should be visible in select
        $this->assertSelectorExists('select#address_select');
        // Now by default NIG should be selected if no address is provided in query
        $this->assertSelectorTextContains('select#address_select option[selected]', 'Standardadresse: Geestland');

        // Now select the address via query params
        $client->request('GET', '/?lat=53.123&lng=8.123');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('select#address_select option[selected]', 'Test Home (Test City)');
    }

    public function testIndexAsGuestIgnoresQueryParams(): void
    {
        $client = static::createClient();
        // Versuche, andere Koordinaten einzuschleusen
        $client->request('GET', '/?lat=50.0&lng=10.0');

        $this->assertResponseIsSuccessful();

        // Überprüfen, ob die lat/lng Inputs vorhanden sind aber die Werte fest bleiben
        $this->assertSelectorExists('input#lat[value="'.$this->getContainer()->getParameter('nig_lat').'"]');
        $this->assertSelectorExists('input#lng[value="'.$this->getContainer()->getParameter('nig_lng').'"]');
        // Wir suchen spezifisch nach dem div, das NICHT Teil der Fehlermeldung ist
        $this->assertSelectorTextContains('div.mb-8.p-5 div.font-bold', 'Niedersächsisches Internatsgymnasium');
    }

    public function testIndexAsUserWithoutAddressesUsesEnvCoordinates(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $testUser = new \App\Entity\User();
        $testUser->setEmail('test_no_address_'.uniqid().'@example.com');
        $testUser->setPassword('password');

        $entityManager->persist($testUser);
        $entityManager->flush();

        $client->loginUser($testUser);
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        // Should use defaults from .env (ParameterBag)
        // We can't easily check the internal state, but we can check if the hidden inputs have the default values
        $expectedLat = static::getContainer()->getParameter('nig_lat');
        $expectedLng = static::getContainer()->getParameter('nig_lng');

        $this->assertSelectorExists('input#lat[value="'.$expectedLat.'"]');
        $this->assertSelectorExists('input#lng[value="'.$expectedLng.'"]');
    }

    public function testIndexShowsApiError(): void
    {
        $client = static::createClient();

        // Wir müssen den Service mocken, um einen Fehler zu simulieren.
        // Da es ein Integrationstest ist, können wir den Container nutzen.
        $tankerkoenigService = $this->createMock(\App\Service\TankerkoenigService::class);
        $tankerkoenigService->method('fetchStations')->willReturn([]);
        $tankerkoenigService->method('lastRequestFailed')->willReturn(true);

        static::getContainer()->set(\App\Service\TankerkoenigService::class, $tankerkoenigService);

        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('div.bg-red-50');
        $this->assertSelectorTextContains('div.bg-red-50 div.font-bold', 'Dienst vorübergehend eingeschränkt');
    }
}
