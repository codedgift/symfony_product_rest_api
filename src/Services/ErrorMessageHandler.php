<?php

namespace App\Services;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ErrorMessageHandler
{
    public function getValidationErrors(?ConstraintViolationListInterface $violations): array
    {
        if (empty($violations)) {
            return [];
        }

        $errors = [];
        foreach ($violations as $key => $violation) {
            $errorKey = str_replace(['[', ']'], '', $violation->getPropertyPath());
            if (!empty($errorKey)) {
                $errors[$errorKey][] = $violation->getMessage();
            }
        }

        return $errors;
    }

  /**
   * Return exception message.
   */
    public function exceptionError(Exception $e): string
    {
        return $e->getMessage();
    }
}
