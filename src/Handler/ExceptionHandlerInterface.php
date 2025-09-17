<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Handler;

interface ExceptionHandlerInterface
{
    public function supports(): string;

    public function getBody(\Throwable $throwable);

    public function getStatusCode(\Throwable $throwable): int;

    public function getHeaders(\Throwable $throwable): array;
}
