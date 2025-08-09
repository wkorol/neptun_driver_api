<?php

declare(strict_types=1);

namespace App\Controller;

use App\User\Domain\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function __invoke(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];
        $email = $payload['email'] ?? null;
        $plain = $payload['password'] ?? null;

        if (!is_string($email) || !is_string($plain) || strlen($plain) < 8) {
            return new JsonResponse(['message' => 'Invalid payload'], 400);
        }

        $existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            return new JsonResponse(['message' => 'User already exists'], 409);
        }

        $user = new User($email);
        $hashed = $passwordHasher->hashPassword($user, $plain);
        $user->setPassword($hashed);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'Registered'], 201);
    }
}
