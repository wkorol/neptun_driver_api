<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OrderListTokenValidator
{
    private const PUBLIC_ORDER_LIST_ID = '3f9b0e1b-1616-4be0-962b-aa63409d4650';

    public function __construct(private OrderListLinkService $linkService)
    {
    }

    public function denyUnlessValid(Request $request, bool $allowPublic = true): ?JsonResponse
    {
        if ($allowPublic && $this->isPublicAccess($request)) {
            return null;
        }

        $token = $this->extractToken($request);
        if ($token === null) {
            return new JsonResponse(['error' => 'Missing token'], 403);
        }

        $expected = $this->linkService->getToken();
        if (!hash_equals($expected, $token)) {
            return new JsonResponse(['error' => 'Invalid token'], 403);
        }

        return null;
    }

    private function extractToken(Request $request): ?string
    {
        $token = $request->headers->get('X-Order-List-Token');
        if (is_string($token) && $token !== '') {
            return $token;
        }

        $token = $request->query->get('token');
        if (is_string($token) && $token !== '') {
            return $token;
        }

        return null;
    }

    private function isPublicAccess(Request $request): bool
    {
        $publicHeader = $request->headers->get('X-Order-List-Public');
        if (is_string($publicHeader) && $publicHeader === self::PUBLIC_ORDER_LIST_ID) {
            return true;
        }

        $publicToken = $request->query->get('publicToken');
        if (is_string($publicToken) && $publicToken === self::PUBLIC_ORDER_LIST_ID) {
            return true;
        }

        return false;
    }
}
