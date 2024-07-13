<?php

namespace App\Services;

use App\Entity\Product;
use App\Exception\CustomHttpException;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductService
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function getProducts(ProductRepository $productRepository): stdClass
    {
        $payload = new stdClass();
        $payload->products = $productRepository->findAll();
        $payload->status = JsonResponse::HTTP_OK;

        return $payload;
    }

    public function getProduct(ProductRepository $productRepository, string $productId): stdClass
    {
        $payload = new stdClass();
        $product = $productRepository->find($productId);
        if (!$product) {
            throw new CustomHttpException(JsonResponse::HTTP_NOT_FOUND, 'Product not found');
        }

        $payload->product = $product;
        $payload->status = JsonResponse::HTTP_OK;

        return $payload;
    }

    public function createProduct(array $data, EntityManagerInterface $entityManager): stdClass
    {
        $violations = $this->validate($data);
        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            $errorMessage = json_encode($errors, JSON_THROW_ON_ERROR);
            throw new CustomHttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $errorMessage);
        }

        /** @var Product $product */
        $product = new Product();
        $product->setName((string) $data['name'])
            ->setDescription((string) $data['description'])
            ->setPrice((string) $data['price'])
            ->setQuantity((int) $data['quantity']);
        $entityManager->persist($product);
        $entityManager->flush();

        $payload = new stdClass();
        $payload->product = $product;
        $payload->status = JsonResponse::HTTP_CREATED;
        $payload->message = "Product created successfully";

        return $payload;
    }

    public function updateProduct(
        string $productId,
        ProductRepository $productRepository,
        array $data,
        EntityManagerInterface $entityManager
    ): stdClass {
        $product = $productRepository->find($productId);
        if (!$product) {
            throw new CustomHttpException(JsonResponse::HTTP_NOT_FOUND, 'Product not found');
        }

        $violations = $this->validate($data);
        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            $errorMessage = json_encode($errors, JSON_THROW_ON_ERROR);
            throw new CustomHttpException(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $errorMessage);
        }

        /** @var Product $product */
        $product->setName((string) $data['name'])
            ->setDescription((string) $data['description'])
            ->setPrice((string) $data['price'])
            ->setQuantity((int) $data['quantity']);
        $entityManager->persist($product);
        $entityManager->flush();

        $payload = new stdClass();
        $payload->product = $product;
        $payload->status = JsonResponse::HTTP_OK;
        $payload->message = "Product updated successfully";

        return $payload;
    }

    public function deleteProduct(
        string $productId,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ): stdClass {
        $product = $productRepository->find($productId);
        if (!$product) {
            throw new CustomHttpException(JsonResponse::HTTP_NOT_FOUND, 'Product not found');
        }

        $entityManager->remove($product);
        $entityManager->flush();

        $payload = new stdClass();
        $payload->message = 'Product deleted successfully';
        $payload->status = JsonResponse::HTTP_OK;

        return $payload;
    }

    public function validate(array $data): ConstraintViolationListInterface
    {
        return $this->validator->validate($data, new Collection([
            'name' => [
                new Length(['min' => 4]),
                new NotBlank(),
            ],
            'description' => [
                new Length(['min' => 4]),
                new NotBlank(),
            ],
            'price' => [
                new NotBlank(),
            ],
            'quantity' => [
                new NotBlank(),
            ]
        ]));
    }
}
