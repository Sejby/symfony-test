<?php

namespace App\Controller;

use App\Service\JobService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private $jobService;

    public function __construct(JobService $jobService)
    {
        $this->jobService = $jobService;
    }

    #[Route('/', name: 'app_api')]
    public function index(Request $request): Response
    {
        $limit = 10; // Počet záznamů na stránku
        $page = $request->query->getInt('page', 1);

        $jobs_data = $this->jobService->fetchJobs($page, $limit);

        $jobs = $jobs_data['jobs'];
        $total_jobs = $jobs_data['total_jobs'];

        // Zde můžete dále zpracovat získané pracovní inzeráty

        return $this->render('index.html.twig', [
            'controller_name' => 'ApiController',
            'jobs' => $jobs,
            'total_jobs' => $total_jobs,
            'current_page' => $page,
            'total_pages' => ceil($total_jobs / $limit),
        ]);
    }

    #[Route('/job/{id}', name: 'job_detail')]
    public function jobDetail(int $id): Response
    {
        $job = $this->jobService->fetchJobDetail($id);

        return $this->render('job_detail.html.twig', [
            'job' => $job,
        ]);
    }
}
