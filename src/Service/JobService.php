<?php

namespace App\Service;

use App\Mapper\JobMapper;
use App\Model\Job;
use Exception;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JobService
{
    private $client;
    private $jobMapper;
    private $accessToken;

    public function __construct(HttpClientInterface $client, JobMapper $jobMapper)
    {
        $this->client = $client;
        $this->jobMapper = $jobMapper;
        $this->accessToken = "89d985c4b1c25c26fe3b1595b4ef3137a0ebb549.11169.dd37716503850db285a143eeef3dd663";
    }

    public function fetchJobs(int $page, int $limit): array
{
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
}


    public function fetchJobDetail(int $id): Job
    {
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
            $job = $this->jobMapper->map($data['payload']);

            return $job;

        } catch (ClientException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
