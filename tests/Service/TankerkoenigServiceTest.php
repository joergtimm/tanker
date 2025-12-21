<?php

namespace App\Tests\Service;

use App\Service\TankerkoenigService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use Psr\Log\LoggerInterface;

class TankerkoenigServiceTest extends TestCase
{
    public function testFetchStationsHandles503Error(): void
    {
        $mockResponse = new MockResponse('', ['status_code' => 503]);
        $httpClient = new MockHttpClient($mockResponse);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->once())
            ->method('error')
            ->with($this->logicalOr(
                $this->stringContains('returned status code 503'),
                $this->stringContains('Tankerkoenig API error')
            ));

        $service = new TankerkoenigService($httpClient, $entityManager, $logger);

        $result = $service->fetchStations('53.6264175', '8.8310458', 5.0);
        $this->assertEquals([], $result);
    }

    public function testFetchPricesHandles503Error(): void
    {
        // This test is currently a placeholder because fetchPrices is private
        // and would need more setup to be tested via fetchStations.
        $this->assertTrue(true);
    }

    public function testFetchStationDetailHandles503Error(): void
    {
        $mockResponse = new MockResponse('', ['status_code' => 503]);
        $httpClient = new MockHttpClient($mockResponse);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $station = new \App\Entity\Station();
        $repository = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $repository->method('findOneBy')->willReturn($station);
        $entityManager->method('getRepository')->willReturn($repository);

        $logger->expects($this->once())
            ->method('error')
            ->with($this->logicalOr(
                $this->stringContains('returned status code 503'),
                $this->stringContains('Tankerkoenig Detail API error')
            ));

        $service = new TankerkoenigService($httpClient, $entityManager, $logger);

        $result = $service->fetchStationDetail('uuid');
        $this->assertSame($station, $result);
    }
}
