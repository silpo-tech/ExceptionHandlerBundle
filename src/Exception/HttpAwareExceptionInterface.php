<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Exception;

interface HttpAwareExceptionInterface extends \Throwable
{
    public function getStatusCode(): int;

    public function getHeaders(): array;
}
