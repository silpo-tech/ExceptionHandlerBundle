<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Tests\EventListener;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ExceptionHandlerBundle\EventListener\ExceptionListener;
use ExceptionHandlerBundle\Handler\ExceptionHandler;
use ExceptionHandlerBundle\Handler\ExceptionHandlerResolver;
use ExceptionHandlerBundle\Tests\Stub\HttpExceptionStub;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends TestCase
{
    public static function providerOnKernelException(): iterable
    {
        yield 'default with HttpExceptionInterface' => [
            'loggerMethod' => 'critical',
            'exception' => new HttpExceptionStub(503, []),
            'expected' => [
                'content' => json_encode([
                    'code' => 503,
                    'message' => 'Sorry, something went wrong. We cannot complete this operation at this time.',
                    'requestId' => '123123',
                ]),
                'statusCode' => 503,
            ],
        ];
        yield 'bad request with HttpExceptionInterface' => [
            'loggerMethod' => 'debug',
            'exception' => new HttpExceptionStub(400, []),
            'expected' => [
                'content' => json_encode([
                    'code' => 400,
                    'message' => '',
                    'requestId' => '123123',
                ]),
                'statusCode' => 400,
            ],
        ];
        yield 'bad request simple Exception' => [
            'loggerMethod' => 'error',
            'exception' => new \Exception('Simple exception', 555),
            'expected' => [
                'content' => json_encode([
                    'code' => 500,
                    'message' => 'Simple exception',
                    'requestId' => '123123',
                ]),
                'statusCode' => 500,
            ],
        ];
    }

    #[DataProvider('providerOnKernelException')]
    public function testOnKernelException(string $loggerMethod, \Throwable $exception, array $expected)
    {
        $exceptionHandlerResolver = new ExceptionHandlerResolver();
        $exceptionHandlerResolver->addExceptionHandler(new ExceptionHandler());

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method($loggerMethod);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $request->headers->set('x-request-id', '123123');

        $exceptionEvent = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

        $exceptionListener = new ExceptionListener($exceptionHandlerResolver, $logger);
        $exceptionListener->onKernelException($exceptionEvent);

        $response = $exceptionEvent->getResponse();

        $this->assertEquals(
            $expected['content'],
            $response->getContent(),
        );

        $this->assertEquals($expected['statusCode'], $response->getStatusCode());
    }
}
