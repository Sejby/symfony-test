<?php

namespace App\Mapper;

use App\Model\Job;

class JobMapper
{
    /**
     * Mapuje pole dat na instanci Job.
     *
     * @param array $data
     * @return Job
     */
    public function map(array $data): Job
    {
        return new Job(
            $data['job_id'],
            $data['title'],
            $data['description'],
            $data['date_created']
        );
    }

    /**
     * Mapuje pole dat na pole instancí Job.
     *
     * @param array $data
     * @return Job[]
     */
    public function mapCollection(array $data): array
    {
        return array_map([$this, 'map'], $data);
    }
}
