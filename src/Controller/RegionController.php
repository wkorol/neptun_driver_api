<?php

namespace App\Controller;

use App\Entity\Region;
use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegionController extends AbstractController
{
    public function __construct(
        private readonly RegionRepository $regionRepository,
    ) {
    }

    #[Route('/region', name: 'app_region', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->regionRepository->findAll()
        );
    }

    #[Route('/region/add', name: 'add_region', methods: ['POST'])]
    public function addRegion(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $region = new Region(
            $data['id'],
            $data['name']
        );

        try {
            $this->regionRepository->addRegion($region);
        } catch (\PDOException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'message' => 'Region added successfully',
            'region' => [
                'id' => $region->getId(),
                'name' => $region->getName(),
            ]
        ], Response::HTTP_CREATED);
    }
}
