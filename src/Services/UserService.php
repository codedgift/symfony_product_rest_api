<?php

namespace App\Services;

use App\Entity\User;
use stdClass;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private ValidatorInterface $validator;
    private stdClass $payload;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->payload = new stdClass();
    }

    public function validate(array $data): ConstraintViolationListInterface
    {
        return $this->validator->validate($data, new Collection([
          'email' => [
              new Email(),
              new NotBlank(),
          ],
          'username' => [
              new Length(['min' => 4]),
              new NotBlank(),
          ],
          'password' => [
              new Length(['min' => 8]),
              new NotBlank(),
          ]
        ]));
    }

    public function register(
        array $data,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): stdClass {
        try {
            $user = new User();
            $user->setEmail((string) $data['email'])
                ->setUsername((string) $data['username']);
            $user->setPassword($passwordHasher->hashPassword($user, (string) $data['password']));
            $entityManager->persist($user);
            $entityManager->flush();

            $this->payload->message = "User registered successfully";
            $this->payload->status = 201;

            return $this->payload;
        } catch (UniqueConstraintViolationException $e) {
            $this->payload->status_code = "False";
            $this->payload->message = "User with this email or username already exists";
            $this->payload->status = 409; // Conflict

            return $this->payload;
        } catch (\Exception $e) {
            $this->payload->message = "something went error - " . $e->getMessage();
            $this->payload->status = 500;

            return $this->payload;
        }
    }
}
