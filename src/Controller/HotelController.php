<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Repository\HotelRepository;
use App\Repository\LumpSumsRepository;
use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HotelController extends AbstractController
{
    public function __construct(private HotelRepository $hotelRepository, private RegionRepository $regionRepository, private LumpSumsRepository $lumpSumsRepository)
    {
    }

    #[Route('/hotel', name: 'app_hotel')]
    public function index(): JsonResponse
    {
        return new JsonResponse($this->hotelRepository->findAll());
    }

    #[Route('/hotel/add', name: 'add_hotel')]
    public function addHotel(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $region = $this->regionRepository->find($data['regionId']);
        $lumpSums = $this->lumpSumsRepository->find($data['lumpSumsId']);
        $newLumpSums = isset($data['newLumpSumsId'])
            ? $this->lumpSumsRepository->find($data['newLumpSumsId'])
            : null;

        if (!$region || !$lumpSums) {
            return new JsonResponse(['error' => 'Invalid Region or LumpSums ID'], Response::HTTP_BAD_REQUEST);
        }

        $hotel = new Hotel(
            $data['name'],
            $region,
            $lumpSums,
            isset($data['lumpSumsExpireDate']) ? new \DateTimeImmutable($data['lumpSumsExpireDate']) : null,
            $newLumpSums
        );

        $this->hotelRepository->addHotel($hotel);
        return new JsonResponse(['message' => 'Created hotel with id ' . $hotel->getId()], Response::HTTP_CREATED);
    }

    #[Route('/hotel/{id}/edit', name: 'edit_hotel', methods: ['PUT'])]
    public function editHotel(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Find the existing hotel
        $existingHotel = $this->hotelRepository->find($id);
        if (!$existingHotel) {
            return new JsonResponse(['error' => 'Hotel not found'], Response::HTTP_NOT_FOUND);
        }
        $this->hotelRepository->updateHotel(
            $existingHotel,
            $data
        );

        return new JsonResponse(['message' => 'Hotel updated successfully'], Response::HTTP_OK);
    }


}
