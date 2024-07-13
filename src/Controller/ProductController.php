<?php

namespace App\Controller;

use App\Exception\CustomHttpException;
use App\Repository\ProductRepository;
use App\Services\ErrorMessageHandler;
use App\Services\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/products', name: 'products_api')]
class ProductController extends AbstractController
{
    private ProductService $productService;
    private ErrorMessageHandler $errorMessageHandler;

    public function __construct(ProductService $productService, ErrorMessageHandler $errorMessageHandler)
    {
        $this->productService = $productService;
        $this->errorMessageHandler = $errorMessageHandler;
    }

    #[Route('/', name: 'get_products', methods: ['GET'])]
    public function index(ProductRepository $productRepository): JsonResponse
    {
        try {
            $payload = $this->productService->getProducts($productRepository);
            return $this->json(['products' => $payload->products], $payload->status);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An unexpected error occurred.'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'get_product', methods: ['GET'])]
    public function show(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $productId = $request->attributes->get('id');

        if (!is_string($productId)) {
            throw new CustomHttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid product ID.');
        }

        try {
            $payload = $this->productService->getProduct($productRepository, $productId);
            return $this->json(['product' => $payload->product], $payload->status);
        } catch (CustomHttpException $e) {
            return $this->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An unexpected error occurred.'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/', name: 'create_product', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            throw new CustomHttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid data format.');
        }

        $violations = $this->productService->validate($data);

        if ($violations->count() > 0) {
            $errors = $this->errorMessageHandler->getValidationErrors($violations);
            return $this->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $payload = $this->productService->createProduct($data, $entityManager);
            return $this->json(['product' => $payload->product, 'message' => $payload->message], $payload->status);
        } catch (CustomHttpException $e) {
            return $this->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An unexpected error occurred.'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update_product', methods: ['PUT'])]
    public function update(
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $productId = $request->attributes->get('id');
        if (!is_string($productId)) {
            throw new CustomHttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid product ID.');
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            throw new CustomHttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid data format.');
        }

        $violations = $this->productService->validate($data);

        if ($violations->count() > 0) {
            $errors = $this->errorMessageHandler->getValidationErrors($violations);
            return $this->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $payload = $this->productService->updateProduct($productId, $productRepository, $data, $entityManager);
            return $this->json(['product' => $payload->product, 'message' => $payload->message], $payload->status);
        } catch (CustomHttpException $e) {
            return $this->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'An unexpected error occurred.'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete_product', methods: ['DELETE'])]
    public function delete(
        Request $request,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $productId = $request->attributes->get('id');
        if (!is_string($productId)) {
            throw new CustomHttpException(JsonResponse::HTTP_BAD_REQUEST, 'Invalid product ID.');
        }

        try {
            $payload = $this->productService->deleteProduct($productId, $productRepository, $entityManager);
            return $this->json(['message' => $payload->message], $payload->status);
        } catch (CustomHttpException $e) {
            return $this->json(['message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json([
                    'message' => 'An unexpected error occurred.'
                ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
