<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Exception;

use SilpoTech\ExceptionHandlerBundle\ExceptionHandlerMessages;

interface ValidationExceptionInterface
{
    public function __construct(iterable $errors, string $message = ExceptionHandlerMessages::VALIDATION_ERROR);

    public function getErrors(): iterable;
}
