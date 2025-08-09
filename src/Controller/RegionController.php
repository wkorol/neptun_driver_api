<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Region;
use App\Region\Repository\RegionRepository;
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
            $this->regionRepository->all()
        );
    }

    #[Route('/region/{id}/hotels', name: 'app_region_hotels', methods: ['GET'])]
    public function getHotels(int $id): JsonResponse
    {
        $region = $this->regionRepository->findById($id);
        $hotels = $region->getHotelsSortedByName();
        return $this->json($hotels);
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
            'message' => 'Rejon dodany poprawnie.',
            'region' => [
                'id' => $region->getId(),
                'name' => $region->getName(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/region/{id}/edit', name: 'edit_region', methods: ['PUT'])]
    public function editRegion(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->regionRepository->editRegion($id, $data);
        return $this->json(['message' => 'Rejon zaktualizowany poprawnie.'], Response::HTTP_OK);
    }

    #[Route('/region/{id}/delete', name: 'remove_region', methods: ['DELETE'])]
    public function removeRegion(int $id): JsonResponse
    {
        $this->regionRepository->removeRegion($id);
        return $this->json(['message' => 'Rejon o id '. $id . 'został usunięty.'], Response::HTTP_OK);
    }
}
