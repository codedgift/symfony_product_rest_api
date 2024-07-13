<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomHttpException extends HttpException
{
    public function __construct(
        int $statusCode,
        ?string $message = null,
        \Throwable $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        parent::__construct($statusCode, $message ?? '', $previous, $headers, $code);
    }

    public function toArray(): array
    {
        return [
            'code' => $this->getStatusCode(),
            'message' => $this->getMessage()
        ];
    }
}
