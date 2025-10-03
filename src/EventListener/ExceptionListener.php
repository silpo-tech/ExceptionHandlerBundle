<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\EventListener;

use Psr\Log\LoggerInterface;
use ExceptionHandlerBundle\Handler\ExceptionHandlerResolver;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final readonly class ExceptionListener
{
    public function __construct(private ExceptionHandlerResolver $handlerResolver, private LoggerInterface $logger)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        // get handler
        $handler = $this->handlerResolver->resolve($throwable);
        $content = $handler->getBody($throwable);
        $statusCode = $handler->getStatusCode($throwable);
        $headers = $handler->getHeaders($throwable);

        if ($requestId = $this->getRequestId($event->getRequest())) {
            $content['requestId'] = $requestId;
        }

        $event->setResponse(new JsonResponse($content, $statusCode, $headers));

        // log
        $f = FlattenException::createFromThrowable($throwable);
        $this->logException(
            $throwable,
            sprintf(
                'Exception thrown when handling an exception (%s: %s at %s line %s)',
                $f->getClass(),
                $f->getMessage(),
                $throwable->getFile(),
                $throwable->getLine(),
            ),
        );
    }

    protected function getRequestId(Request $request): ?string
    {
        return $request->headers->get('x-request-id');
    }

    protected function logException(\Throwable $exception, $message): void
    {
        /* @phpstan-ignore-next-line */
        if (null !== $this->logger) {
            if ($exception instanceof HttpExceptionInterface) {
                if ($exception->getStatusCode() >= 500) {
                    $this->logger->critical($message, ['exception' => $exception]);
                } else {
                    $this->logger->debug($message, ['exception' => $exception]);
                }
            } else {
                $this->logger->error($message, ['exception' => $exception]);
            }
        }
    }
}
