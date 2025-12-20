<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MapControllerTest extends WebTestCase
{
    public function testMapPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/map');

        $this->assertResponseIsSuccessful();

        // Wir suchen nach dem spezifischen Leaflet-Controller oder einem anderen Map-Controller
        $this->assertSelectorExists('[data-controller*="ux-leaflet-map"]');
    }
}
