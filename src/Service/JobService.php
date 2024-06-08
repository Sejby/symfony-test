<?php

namespace App\Service;

use App\Mapper\JobMapper;
use App\Model\Job;
use Exception;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class JobService
{
    private $client;
    private $jobMapper;
    private $accessToken;
    private $cache;

    public function __construct(string $token, HttpClientInterface $client, JobMapper $jobMapper, CacheInterface $cache)
    {
        $this->client = $client;
        $this->jobMapper = $jobMapper;
        $this->accessToken = $token;
        $this->cache = $cache;
    }

    public function fetchJobs(int $page, int $limit): array
    {
        $cacheKey = sprintf('jobs_page_%d_limit_%d', $page, $limit);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($page, $limit) {
            $item->expiresAfter(3600); // Nastavte dobu expirace cache na 1 hodinu

            try {
                $response = $this->client->request(
                    'GET',
                    'https://app.recruitis.io/api2/jobs',
                    [
                        'query' => [
                            'page' => $page,
                            'limit' => $limit,
                        ],
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->accessToken,
                        ],
                    ]
                );

                $data = $response->toArray();

                $jobs = [];
                foreach ($data['payload'] as $jobData) {
                    $job = $this->jobMapper->map($jobData);
                    $jobs[] = $job;
                }

                $total_jobs = $data['meta']['entries_total']; // Celkový počet pracovních inzerátů

                return ['jobs' => $jobs, 'total_jobs' => $total_jobs];

            } catch (ClientException $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        });
    }

    public function fetchJobDetail(int $id): Job
    {
        $cacheKey = sprintf('job_detail_%d', $id);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(3600); // Nastavte dobu expirace cache na 1 hodinu

            try {
                $response = $this->client->request(
                    'GET',
                    sprintf('https://api.example.com/jobs/%d', $id),
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->accessToken,
                        ],
                    ]
                );

                $data = $response->toArray();
                return $this->jobMapper->map($data['payload']);

            } catch (ClientException $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        });
    }
}
