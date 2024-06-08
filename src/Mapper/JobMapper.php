<?php

namespace App\Mapper;

use App\Model\Job;

class JobMapper
{
    public function map(array $data): Job
    {
        return new Job(
            $data['job_id'],
            $data['title'],
            $data['description'],
            $data['draft'],
            $data['active']
        );
    }

    public function mapCollection(array $data): array
    {
        return array_map([$this, 'map'], $data);
    }
}