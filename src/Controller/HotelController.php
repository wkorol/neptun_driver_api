<?php

declare(strict_types=1);

namespace App\Controller;

use App\Hotel\Domain\Hotel;
use App\Hotel\Repository\HotelRepository;
use App\LumpSums\Repository\LumpSumsRepository;
use App\Project\UseCase\AddHotel;
use App\Project\UseCase\RemoveHotel;
use App\Project\UseCase\UpdateHotel;
use App\Project\UseCase\AddHotelHandler;
use App\Project\UseCase\RemoveHotelHandler;
use App\Project\UseCase\UpdateHotelHandler;
use App\Region\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class HotelController extends AbstractController
{
    public function __construct(
        private readonly HotelRepository $hotelRepository,
        private readonly RegionRepository $regionRepository,
        private readonly LumpSumsRepository $lumpSumsRepository,
        private readonly AddHotelHandler $addHotelHandler,
        private readonly RemoveHotelHandler $removeHotelHandler,
        private readonly UpdateHOtelHandler $updateHotelHandler,
    ) {
    }

    #[Route('/hotel', name: 'app_hotel')]
    public function index(): JsonResponse
    {
        return new JsonResponse($this->hotelRepository->all());
    }

    #[Route('/hotel/add', name: 'add_hotel')]
    public function addHotel(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $region = $this->regionRepository->findById($data['regionId']);
        $lumpSums = $this->lumpSumsRepository->find($data['lumpSumsId']);
        $newLumpSums = isset($data['newLumpSumsId'])
            ? $this->lumpSumsRepository->find($data['newLumpSumsId'])
            : null;

        if (!$region || !$lumpSums) {
            return new JsonResponse(['error' => 'Niepoprawne ID rejonu lub ID ryczałtów'], Response::HTTP_BAD_REQUEST);
        }

        $hotel = new Hotel(
            Uuid::v4(),
            $data['name'],
            $region,
            $lumpSums,
            isset($data['lumpSumsExpireDate']) ? new \DateTimeImmutable($data['lumpSumsExpireDate']) : null,
            $newLumpSums
        );

        try {
            $this->addHotelHandler->__invoke(new AddHotel\Command($hotel));
        } catch (\PDOException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return new JsonResponse(['message' => 'Utworzono hotel o ID ' . $hotel->getId()], Response::HTTP_CREATED);
    }

    #[Route('/hotel/{id}/edit', name: 'edit_hotel', methods: ['PUT'])]
    public function editHotel(Uuid $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $this->updateHotelHandler->__invoke(
                new UpdateHotel\Command(
                    $id,
                    $data,
                )
            );
        } catch (\PDOException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'Hotel zaktualizowany poprawnie.'], Response::HTTP_OK);
    }

    #[Route('/hotel/{id}', name: 'get_hotel', methods: ['GET'])]
    public function hotelInfo(Uuid $id): JsonResponse
    {
        $hotel = $this->hotelRepository->findById($id);
        if (!$hotel) {
            return new JsonResponse(['error' => 'Hotel nieznaleziony.'], Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($hotel);
    }

    #[Route('/hotel/{id}/delete', name: 'delete_hotel', methods: ['DELETE'])]
    public function removeHotel(Uuid $id): JsonResponse
    {
        try {
            $this->removeHotelHandler->__invoke(new RemoveHotel\Command($id));
        } catch (\PDOException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return $this->json(['message' => 'Hotel o id ' .$id.  'został usunięty.'], Response::HTTP_OK);
    }
}
