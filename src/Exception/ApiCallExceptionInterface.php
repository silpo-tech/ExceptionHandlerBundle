<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Exception;

interface ApiCallExceptionInterface
{
    public function getContents(): string;
}
