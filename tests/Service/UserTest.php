<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends TestCase
{
    private UserService $userService;
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidator();
        $this->userService = new UserService($this->validator);
    }

    public function testValidate(): void
    {
        $data = [
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password123',
        ];

        $violations = $this->userService->validate($data);

        $this->assertEquals(0, count($violations));
    }

    public function testRegister(): void
    {
        // Mock the password hasher
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')
            ->willReturn('hashedPassword');


        // Mock the entity manager
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(User::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $data = [
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password123',
        ];

        $payload = $this->userService->register($data, $passwordHasher, $entityManager);

        $this->assertEquals(201, $payload->status);
        $this->assertEquals("User registered successfully", $payload->message);
    }

    public function testRegisterWithException(): void
    {
        // Mock the password hasher
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        // Mock the entity manager to throw an exception
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('persist')->will($this->throwException(new \Exception("Database error")));

        $data = [
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password123',
        ];

        $payload = $this->userService->register($data, $passwordHasher, $entityManager);

        $this->assertEquals(500, $payload->status);
        $this->assertStringContainsString("something went error - Database error", $payload->message);
    }
}
