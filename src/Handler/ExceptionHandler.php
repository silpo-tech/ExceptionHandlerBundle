<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Handler;

use ExceptionHandlerBundle\Exception\ApiCallExceptionInterface;
use ExceptionHandlerBundle\Exception\HttpAwareExceptionInterface;
use ExceptionHandlerBundle\ExceptionHandlerMessages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(protected bool $debug = false)
    {
    }

    public function supports(): string
    {
        return \Throwable::class;
    }

    public function getBody(\Throwable $throwable): array
    {
        $statusCode = $this->getStatusCode($throwable);

        $body = [
            'code' => $statusCode,
            'message' => $this->getExceptionMessage($throwable, $statusCode),
        ];

        if (true === $this->debug) {
            $body['exception'] = [
                'message' => $throwable->getMessage(),
                'class' => $throwable::class,
                'file' => $throwable->getFile(),
                'line' => $throwable->getLine(),
                'trace' => $throwable->getTrace(),
            ];

            if (
                ($throwable instanceof ApiCallExceptionInterface)
                && ($response = json_decode($throwable->getContents(), true))
            ) {
                $body['response'] = $response;
            }
        }

        return $body;
    }

    public function getStatusCode(\Throwable $throwable): int
    {
        return ($throwable instanceof HttpExceptionInterface) || ($throwable instanceof HttpAwareExceptionInterface)
            ? $throwable->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function getHeaders(\Throwable $throwable): array
    {
        return ($throwable instanceof HttpExceptionInterface) || ($throwable instanceof HttpAwareExceptionInterface)
            ? $throwable->getHeaders()
            : [];
    }

    protected function getExceptionMessage(\Throwable $throwable, ?int $statusCode = null): string
    {
        $message = $throwable->getMessage();
        if ('' === $message && Response::HTTP_FORBIDDEN == $statusCode) {
            $message = ExceptionHandlerMessages::ACCESS_DENIED;
        }

        if ('' === $message && $statusCode >= 500) {
            $message = ExceptionHandlerMessages::INTERNAL_ERROR;
        }

        return $message;
    }
}
