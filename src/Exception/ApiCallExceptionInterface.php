<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Exception;

interface ApiCallExceptionInterface
{
    public function getContents(): string;
}
