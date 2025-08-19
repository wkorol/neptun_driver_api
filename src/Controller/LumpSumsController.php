<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\FixedPrice;
use App\DTO\Tariff;
use App\LumpSums\Domain\LumpSums;
use App\LumpSums\Repository\LumpSumsRepository;
use App\Project\UseCase\AddLumpSums;
use App\Project\UseCase\AddLumpSumsHandler;
use App\Project\UseCase\RemoveLumpSums;
use App\Project\UseCase\RemoveLumpSumsHandler;
use App\Project\UseCase\UpdateLumpSums;
use App\Project\UseCase\UpdateLumpSumsHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class LumpSumsController extends AbstractController
{
    public function __construct(
        private readonly LumpSumsRepository $lumpSumsRepository,
        private readonly AddLumpSumsHandler $addLumpSumsHandler,
        private readonly RemoveLumpSumsHandler $removeLumpSumsHandler,
        private readonly UpdateLumpSumsHandler $updateLumpSumsHandler,
    ) {
    }

    #[Route('/lump_sums', name: 'lump_sums')]
    public function index(): JsonResponse
    {
        return $this->json($this->lumpSumsRepository->all());
    }

    #[Route('/lump_sums/add', name: 'add_lump_sums', methods: ['POST'])]
    public function addLumpSums(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['name'], $data['fixedValues'])) {
            return $this->json([
                'message' => 'Invalid JSON data or missing fields',
            ], Response::HTTP_BAD_REQUEST);
        }

        $values = array_map(
            fn ($data) => new FixedPrice(
                $data['name'],
                Tariff::fromArray($data['tariff1']),
                Tariff::fromArray($data['tariff2']),
            ),
            $data['fixedValues']
        );

        $lumpSums = new LumpSums(
            Uuid::v4(),
            $data['name'],
            $values
        );

        $this->addLumpSumsHandler->__invoke(new AddLumpSums\Command($lumpSums));

        return $this->json([
            'id' => $lumpSums->getId(),
            'message' => 'Utworzono nowy zestaw ryczałtów.',
        ]);
    }

    #[Route('/lump_sums/{id}/delete', name: 'remove_lump_sums', methods: ['DELETE'])]
    public function removeLumpSums(Uuid $id): JsonResponse
    {
        $this->removeLumpSumsHandler->__invoke(new RemoveLumpSums\Command($id));

        return $this->json(['message' => 'Ryczałty o ID '.$id.' zostały usunięte.'], Response::HTTP_OK);
    }

    #[Route('/lump_sums/{id}/edit', name: 'edit_lump_sums', methods: ['PUT'])]
    public function editLumpSums(Uuid $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['name'], $data['fixedValues'])) {
            return $this->json([
                'message' => 'Invalid JSON data or missing fields',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->updateLumpSumsHandler->__invoke(new UpdateLumpSums\Command($id, $data));
        } catch (\Exception $e) {
            return $this->json(['error' => 'Błąd w aktualizacji ryczałtów: '.$e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'message' => 'Lump Sums updated successfully',
        ], Response::HTTP_OK);
    }
}
