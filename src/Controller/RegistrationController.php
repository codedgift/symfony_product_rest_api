<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\CustomHttpException;
use App\Services\ErrorMessageHandler;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/register', name: 'products_api')]
class RegistrationController extends AbstractController
{
    private UserService $userService;
    private ErrorMessageHandler $errorMessageHandler;

    public function __construct(UserService $userService, ErrorMessageHandler $errorMessageHandler)
    {
        $this->userService = $userService;
        $this->errorMessageHandler = $errorMessageHandler;
    }

    #[Route('/', name: 'app_registration')]
    public function index(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new CustomHttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid data format');
        }

        $violations = $this->userService->validate($data);
        $errors = $this->errorMessageHandler->getValidationErrors($violations);
        if (!empty($errors)) {
            return $this->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $this->userService->register($data, $passwordHasher, $entityManager);

        return $this->json([
            'message' => $payload->message
        ], $payload->status);
    }
}
