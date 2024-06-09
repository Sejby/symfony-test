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

    public function __construct(private string $token, private HttpClientInterface $client, private JobMapper $jobMapper, private CacheInterface $cache)
    {}

    /**
     * Získá inzeráty s cachováním.
     *
     * @param int $page
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function fetchJobs(int $page, int $limit): array
    {
        $cache_key = sprintf('jobs_page_%d_limit_%d', $page, $limit);
    
        try {
            return $this->cache->get($cache_key, function (ItemInterface $item) use ($page, $limit) {
                $item->expiresAfter(3600);
    
                $response = $this->client->request(
                    'GET',
                    'https://app.recruitis.io/api2/jobs',
                    [
                        'query' => [
                            'page' => $page,
                            'limit' => $limit,
                        ],
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->token,
                        ],
                    ]
                );
    
                $data = $response->toArray();
    
                if (empty($data['payload']) || !isset($data['meta']['entries_total'])) {
                    return ['jobs' => [], 'total_jobs' => 0];
                }
    
                $jobs = [];
                foreach ($data['payload'] as $jobData) {
                    $job = $this->jobMapper->map($jobData);
                    $jobs[] = $job;
                }
    
                $total_jobs = $data['meta']['entries_total'];
    
                return ['jobs' => $jobs, 'total_jobs' => $total_jobs];
            });
        } catch (ClientException $e) {
            throw new Exception('Neúspěšné získání dat z API: ' . $e->getMessage(), $e->getCode());
            
        }
    }
    
    


    /**
     * Získá detail inzerátu s cachováním.
     *
     * @param int $id
     * @return Job
     * @throws Exception
     */
    public function fetchJobDetail(int $id): Job
    {
        $cacheKey = sprintf('job_detail_%d', $id);

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(3600); // Uloží data do cache, které expirují po 1 hodině

            try {
                $response = $this->client->request(
                    'GET',
                    sprintf('https://app.recruitis.io/api2/jobs/%d', $id),
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->token,
                        ],
                    ]
                );

                $data = $response->toArray();
                return $this->jobMapper->map($data['payload']);

            } catch (ClientException $e) {
                throw new Exception('Neúspěšné získání dat z API: ' . $e->getMessage(), $e->getCode());
            }
        });
    }
}
