<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Exception;

use ExceptionHandlerBundle\ExceptionHandlerMessages;

interface ValidationExceptionInterface
{
    public function __construct(iterable $errors, string $message = ExceptionHandlerMessages::VALIDATION_ERROR);

    public function getErrors(): iterable;
}
