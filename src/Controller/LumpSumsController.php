<?php

namespace App\Controller;

use App\DTO\FixedPrice;
use App\DTO\Tariff;
use App\Entity\LumpSums;
use App\Repository\LumpSumsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class LumpSumsController extends AbstractController
{
    public function __construct(private readonly LumpSumsRepository $lumpSumsRepository)
    {
    }

    #[Route('/lump_sums', name: 'lump_sums')]
    public function index(): JsonResponse
    {
        return $this->json($this->lumpSumsRepository->findAll());
    }

    #[Route('/lump_sums/add', name: 'add_lump_sums', methods: ['POST'])]
    public function addLumpSums(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['name'], $data['fixedValues'])) {
            return $this->json([
                'message' => 'Invalid JSON data or missing fields'
            ], Response::HTTP_BAD_REQUEST);
        }

        $values = array_map(
            fn($data) => new FixedPrice(
                $data['name'],
                Tariff::fromArray($data['tariff1']),
                Tariff::fromArray($data['tariff2']),
            ),
            $data['fixedValues']
        );

        $fixedPrice = new LumpSums(
            $data['name'],
            $values
        );

        $this->lumpSumsRepository->addLumpSums($fixedPrice);

        return $this->json([
            'id' => $fixedPrice->getId(),
            'message' => 'Utworzono nowy zestaw ryczałtów.'
        ]);
    }

    #[Route('/lump_sums/{id}/delete', name: 'remove_lump_sums', methods: ['DELETE'])]
    public function removeRegion(Uuid $id): JsonResponse
    {
        $this->lumpSumsRepository->removeLumpSums($id);
        return $this->json(['message' => 'Ryczałty o ID '. $id . ' zostały usunięte.'], Response::HTTP_OK);
    }

    #[Route('/lump_sums/{id}/edit', name: 'edit_lump_sums', methods: ['PUT'])]
    public function editLumpSums(Uuid $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate the input data
        if (!$data || !isset($data['name'], $data['fixedValues'])) {
            return $this->json([
                'message' => 'Invalid JSON data or missing fields'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Find the existing LumpSums entity
        $existingLumpSum = $this->lumpSumsRepository->find($id);
        if (!$existingLumpSum) {
            return $this->json(['error' => 'Ryczałty nieznalezione.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->lumpSumsRepository->updateLumpSums($existingLumpSum, $data);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Błąd w aktualizacji ryczałtów: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'message' => 'Lump Sums updated successfully',
            'data' => $existingLumpSum
        ], Response::HTTP_OK);
    }

}
