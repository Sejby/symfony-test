<?php

namespace App\Controller;

use App\Service\JobService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JobController extends AbstractController
{

    public function __construct(private JobService $jobService)
    {}

    // Vrátí úvodní stránku s inzeráty
    #[Route('/', name: 'jobs')]
    public function index(Request $request): Response
    {
        $limit = 10; // Počet inzerátů na stránku, který chceme zobrazit
        $page = max(1, $request->query->getInt('page', 1)); // Ošetření minimální hodnoty stránky

        // Vrátí z JobSevice pole s inzeráty a celkovým počtem inzerátů, kde inzeráty jsou namapovány na třídu Job
        $jobs_data = $this->jobService->fetchJobs($page, $limit);
        
        // Jednotlivé inzeráty
        $jobs = $jobs_data['jobs'];

        // Celkový počet inzerátů
        $total_jobs = $jobs_data['total_jobs'];
        
        // Celkový počet stránek jako celkový počet inzerátů / počet inzerátů na stránku zaokrouhlený nahoru
        $total_pages = (int) ceil($total_jobs / $limit); 

        // Vykreslení do šablony s potřebnými proměnnými
        return $this->render('jobs/jobs.html.twig', [
            'controller_name' => 'JobController',
            'jobs' => $jobs,
            'total_jobs' => $total_jobs,
            'current_page' => $page,
            'total_pages' => $total_pages,
        ]);
    }

    // Vrátí detail jednotlivého inzerátu
    #[Route('/job/{id}', name: 'job_detail', requirements: ['id' => '\d+'])]
    public function jobDetail(int $id, Request $request): Response
    {
        // Získá detail inzerátu podle ID z JobService (mapováno na třídu Job)
        $job = $this->jobService->fetchJobDetail($id);

        // Získá aktuální stránku
        $page = $request->query->getInt('page', 1);

        // Vykreslení do šablony s potřebnou proměnnou
        return $this->render('jobs/job_detail.html.twig', [
            'job' => $job,
            'page' => $page,
        ]);
    }
}
