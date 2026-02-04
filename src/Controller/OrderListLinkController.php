<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\OrderListLinkService;
use App\Service\OrderListTokenValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrderListLinkController extends AbstractController
{
    public function __construct(
        private OrderListLinkService $linkService,
        private OrderListTokenValidator $tokenValidator,
    )
    {
    }

    #[Route('/order-list/link', name: 'order_list_link', methods: ['GET'])]
    public function getLink(Request $request): JsonResponse
    {
        if ($denied = $this->tokenValidator->denyUnlessValid($request, false)) {
            return $denied;
        }

        $token = $this->linkService->getToken();
        $origin = $this->resolveOrigin($request);

        return new JsonResponse([
            'token' => $token,
            'link' => $this->buildLink($origin, $token),
        ]);
    }

    #[Route('/order-list/link/reset', name: 'order_list_link_reset', methods: ['GET'])]
    public function resetLink(Request $request): JsonResponse
    {
        if ($this->linkService->hasToken()) {
            if ($denied = $this->tokenValidator->denyUnlessValid($request, false)) {
                return $denied;
            }
        }

        $token = $this->linkService->resetToken();
        $origin = $this->resolveOrigin($request);

        return new JsonResponse([
            'token' => $token,
            'link' => $this->buildLink($origin, $token),
        ]);
    }

    private function resolveOrigin(Request $request): string
    {
        $allowed = [
            'http://localhost:4200',
            'https://frontend-neptun-o59eg.ondigitalocean.app',
        ];

        $origin = $request->headers->get('origin');
        if (is_string($origin) && $origin !== '' && in_array($origin, $allowed, true)) {
            return $origin;
        }

        $host = $request->getHost();
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return 'http://localhost:4200';
        }

        return 'https://frontend-neptun-o59eg.ondigitalocean.app';
    }

    private function buildLink(string $origin, string $token): string
    {
        return rtrim($origin, '/').'/'.$token;
    }
}
