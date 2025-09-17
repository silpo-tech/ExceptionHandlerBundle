<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Tests\Stub;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class HttpExceptionStub extends \Exception implements HttpExceptionInterface
{
    public function __construct(
        private int $statusCode,
        private array $headers,
        $message = '',
        $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
        $this->file = 'filename.php';
        $this->line = 300;
        $exceptionReflection = new \ReflectionObject($this);
        while (false !== $exceptionReflection->getParentClass()) {
            $exceptionReflection = $exceptionReflection->getParentClass();
        }
        $traceReflection = $exceptionReflection->getProperty('trace');
        $traceReflection->setAccessible(true);
        $traceReflection->setValue($this, []);
        $traceReflection->setAccessible(false);
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
