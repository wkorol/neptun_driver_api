<?php

declare(strict_types=1);

namespace App\Controller;

use App\TaxiService\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController extends AbstractController
{
    public function __construct(private ServiceRepository $serviceRepository)
    {
    }

    #[Route('/service', name: 'app_service')]
    public function index(): JsonResponse
    {
        return new JsonResponse($this->serviceRepository->all());
    }
}
