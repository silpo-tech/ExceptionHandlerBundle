<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Handler;

class ExceptionHandlerResolver
{
    /**
     * @var ExceptionHandlerInterface[]
     */
    private array $handlers;

    public function addExceptionHandler(ExceptionHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    public function resolve(\Throwable $e): ?ExceptionHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            $supports = $handler->supports();
            if ($e instanceof $supports) {
                return $handler;
            }
        }

        return null;
    }
}
