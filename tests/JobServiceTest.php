<?php

namespace App\Tests;

use App\Mapper\JobMapper;
use App\Model\Job;
use App\Service\JobService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class JobServiceTest extends TestCase
{
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = getenv('API_TOKEN') ?: 'fake_token'; // Použijeme fake token, pokud není k dispozici v prostředí
    }

    public function testFetchJobs(): void
    {
        // Arrange
        $httpClient = $this->createMock(HttpClientInterface::class);
        $jobMapper = $this->createMock(JobMapper::class);
        $cache = $this->createMock(CacheInterface::class);

        // Mockovaná odpověď z API
        $jobsData = [
            'payload' => [
                ['job_id' => 1, 'title' => 'Job 1', 'description' => 'Description 1', 'date_created' => '2021-10-01'],
                ['job_id' => 2, 'title' => 'Job 2', 'description' => 'Description 2', 'date_created' => '2021-10-02'],
            ],
            'meta' => ['entries_total' => 2]
        ];
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($jobsData);

        // Nastavení chování mock objektů
        $httpClient->method('request')->willReturn($response);

        // Simulujeme chování cache, aby zavolala callback a vrátila skutečné data z API
        $cache->method('get')->willReturnCallback(function ($key, $callback) {
            $item = $this->createMock(ItemInterface::class);
            return $callback($item);
        });

        // Mockování mapování práce
        $jobMapper->method('map')->willReturnCallback(function ($jobData) {
            return new Job($jobData['job_id'], $jobData['title'], $jobData['description'], $jobData['date_created']); // Zde můžete přizpůsobit mapování podle skutečného Job modelu
        });

        // Inicializace služby
        $jobService = new JobService($this->token, $httpClient, $jobMapper, $cache);

        // Act
        $result = $jobService->fetchJobs(1, 10);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('jobs', $result);
        $this->assertArrayHasKey('total_jobs', $result);
        $this->assertNotEmpty($result['jobs']);
        $this->assertCount(2, $result['jobs']);
        $this->assertIsInt($result['total_jobs']);
        $this->assertEquals(2, $result['total_jobs']);
    }
}
