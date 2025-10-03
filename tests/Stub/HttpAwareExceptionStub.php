<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Tests\Stub;

use ExceptionHandlerBundle\Exception\HttpAwareExceptionInterface;

class HttpAwareExceptionStub extends \Exception implements HttpAwareExceptionInterface
{
    public function __construct(
        private int $statusCode,
        private array $headers,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
