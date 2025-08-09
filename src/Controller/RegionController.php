<?php

declare(strict_types=1);

namespace App\Controller;

use App\Project\UseCase\AddRegion;
use App\Project\UseCase\AddRegionHandler;
use App\Project\UseCase\EditRegion;
use App\Project\UseCase\EditRegionHandler;
use App\Project\UseCase\RemoveRegion;
use App\Project\UseCase\RemoveRegionHandler;
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
        private readonly AddRegionHandler $addRegionHandler,
        private readonly EditRegionHandler $editRegionHandler,
        private readonly RemoveRegionHandler $removeRegionHandler,
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
        if ($region) {
            $hotels = $region->getHotels();
        }

        return $this->json($hotels ?? []);
    }

    #[Route('/region/add', name: 'add_region', methods: ['POST'])]
    public function addRegion(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $region = $this->addRegionHandler->__invoke(new AddRegion\Command(
                (int) $data['id'],
                $data['name'],
            ));
        } catch (\PDOException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'message' => 'Rejon dodany poprawnie.',
            'region' => [
                'id' => $region->getId(),
                'name' => $region->getName(),
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/region/{id}/edit', name: 'edit_region', methods: ['PUT'])]
    public function editRegion(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->editRegionHandler->__invoke(new EditRegion\Command(
            $id,
            $data['name']
        ));

        return $this->json(['message' => 'Rejon zaktualizowany poprawnie.'], Response::HTTP_OK);
    }

    #[Route('/region/{id}/delete', name: 'remove_region', methods: ['DELETE'])]
    public function removeRegion(int $id): JsonResponse
    {
        $this->removeRegionHandler->__invoke(new RemoveRegion\Command($id));

        return $this->json(['message' => 'Rejon o id '.$id.'został usunięty.'], Response::HTTP_OK);
    }
}
