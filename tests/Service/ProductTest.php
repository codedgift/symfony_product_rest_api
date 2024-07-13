<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Exception\CustomHttpException;
use App\Services\ProductService;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

class ProductTest extends TestCase
{
    private ProductService $productService;
    /** @var MockObject&ProductRepository */
    private $productRepository;
    /** @var MockObject&EntityManagerInterface */
    private $entityManager;
    /** @var MockObject&ValidatorInterface */
    private $validator;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->productService = new ProductService($this->validator);
    }

    public function testGetProductsReturnsAllProducts(): void
    {
        $products = [new Product(), new Product()];
        $this->productRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($products);

        $result = $this->productService->getProducts($this->productRepository);

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertCount(2, $result->products);
        $this->assertEquals(200, $result->status);
    }

    public function testGetProductsHandlesException(): void
    {
        $this->productRepository->expects($this->once())
            ->method('findAll')
            ->willThrowException(new \Exception());

        $this->expectException(\Exception::class);

        $this->productService->getProducts($this->productRepository);
    }

    public function testGetProductReturnsProduct(): void
    {
        $product = new Product();
        $product->setName('Test Product')
                ->setDescription('Test Description')
                ->setPrice('100')
                ->setQuantity(10);

        $this->productRepository->expects($this->once())
            ->method('find')
            ->with('123')
            ->willReturn($product);

        $result = $this->productService->getProduct($this->productRepository, '123');

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertInstanceOf(Product::class, $result->product);
        $this->assertEquals('Test Product', $result->product->getName());
        $this->assertEquals(200, $result->status);
    }

    public function testCustomHttpException(): void
    {
        $statusCode = 400;
        $message = 'Invalid request';
        $exception = new CustomHttpException($statusCode, $message);

        $this->assertInstanceOf(CustomHttpException::class, $exception);
        $this->assertEquals($statusCode, $exception->getStatusCode());
        $this->assertEquals($message, $exception->getMessage());
    }

    public function testCustomHttpExceptionWithDefaultValues(): void
    {
        $statusCode = 500;
        $exception = new CustomHttpException($statusCode);

        $this->assertInstanceOf(CustomHttpException::class, $exception);
        $this->assertEquals($statusCode, $exception->getStatusCode());
        $this->assertEquals('', $exception->getMessage());
    }

    public function testCreateProduct(): void
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => '100',
            'quantity' => 10
        ];

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Product::class));
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->productService->createProduct($productData, $this->entityManager);

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertInstanceOf(Product::class, $result->product);
        $this->assertEquals('Test Product', $result->product->getName());
        $this->assertEquals(201, $result->status);
        $this->assertEquals('Product created successfully', $result->message);
    }

    public function testCreateProductWithInvalidData(): void
    {
        $productData = [
            'name' => '',
            'description' => 'Test Description',
            'price' => '100',
            'quantity' => 10
        ];

        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->method('getMessage')->willReturn('This value should not be blank.');
        $violation->method('getPropertyPath')->willReturn('name');

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $violations->expects($this->any())
            ->method('get')
            ->with($this->isType('int'))
            ->willReturn($violation);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($productData)
            ->willReturn($violations);

        $this->expectException(CustomHttpException::class);

        $this->productService->createProduct($productData, $this->entityManager);
    }

    public function testUpdateProduct(): void
    {
        $product = new Product();
        $product->setName('Test Product')
                ->setDescription('Test Description')
                ->setPrice('100')
                ->setQuantity(10);

        $this->productRepository->expects($this->once())
            ->method('find')
            ->with('123')
            ->willReturn($product);

        $productData = [
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => '200',
            'quantity' => 20
        ];

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($product);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->productService->updateProduct(
            '123',
            $this->productRepository,
            $productData,
            $this->entityManager
        );

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertInstanceOf(Product::class, $result->product);
        $this->assertEquals('Updated Product', $result->product->getName());
        $this->assertEquals(200, $result->status);
        $this->assertEquals('Product updated successfully', $result->message);
    }

    public function testUpdateProductWithInvalidData(): void
    {
        $product = new Product();
        $product->setName('Test Product')
                ->setDescription('Test Description')
                ->setPrice('100')
                ->setQuantity(10);

        $this->productRepository->expects($this->once())
            ->method('find')
            ->with('123')
            ->willReturn($product);

        $productData = [
            'name' => '',
            'description' => 'Updated Description',
            'price' => '200',
            'quantity' => 20
        ];

        $violation = $this->createMock(ConstraintViolationInterface::class);
        $violation->method('getMessage')->willReturn('This value should not be blank.');
        $violation->method('getPropertyPath')->willReturn('name');

        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $violations->expects($this->once())
            ->method('count')
            ->willReturn(1);
        $violations->expects($this->any())
            ->method('get')
            ->with($this->isType('int'))
            ->willReturn($violation);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($productData)
            ->willReturn($violations);

        $this->expectException(CustomHttpException::class);

        $this->productService->updateProduct('123', $this->productRepository, $productData, $this->entityManager);
    }

    public function testUpdateProductNotFound(): void
    {
        $this->productRepository->expects($this->once())
            ->method('find')
            ->with('123')
            ->willReturn(null);

        $productData = [
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => '200',
            'quantity' => 20
        ];

        $this->expectException(CustomHttpException::class);

        $this->productService->updateProduct('123', $this->productRepository, $productData, $this->entityManager);
    }

    public function testDeleteProduct(): void
    {
        $product = new Product();
        $product->setName('Test Product')
                ->setDescription('Test Description')
                ->setPrice('100')
                ->setQuantity(10);

        $this->productRepository->expects($this->once())
            ->method('find')
            ->with('123')
            ->willReturn($product);

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($product);
        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->productService->deleteProduct('123', $this->productRepository, $this->entityManager);

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertEquals(200, $result->status);
        $this->assertEquals('Product deleted successfully', $result->message);
    }

    public function testDeleteProductNotFound(): void
    {
        $this->productRepository->expects($this->once())
            ->method('find')
            ->with('123')
            ->willReturn(null);

        $this->expectException(CustomHttpException::class);

        $this->productService->deleteProduct('123', $this->productRepository, $this->entityManager);
    }
}
