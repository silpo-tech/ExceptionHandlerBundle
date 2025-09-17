<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Exception;

use SilpoTech\ExceptionHandlerBundle\ExceptionHandlerMessages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidationException extends HttpException implements ValidationExceptionInterface
{
    public function __construct(private iterable $errors, string $message = ExceptionHandlerMessages::VALIDATION_ERROR)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, $message);
    }

    public function getErrors(): iterable
    {
        return $this->errors;
    }
}
