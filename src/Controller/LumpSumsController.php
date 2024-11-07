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

class LumpSumsController extends AbstractController
{
    public function __construct(private LumpSumsRepository $lumpSumsRepository)
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
            'message' => 'Added new Lump Sums'
        ]);
    }
}
