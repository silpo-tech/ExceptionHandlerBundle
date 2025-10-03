<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Tests\Handler;

use ExceptionHandlerBundle\Handler\ExceptionHandler;
use ExceptionHandlerBundle\Tests\Stub\ApiCallExceptionStub;
use ExceptionHandlerBundle\Tests\Stub\HttpAwareExceptionStub;
use ExceptionHandlerBundle\Tests\Stub\HttpExceptionStub;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ExceptionHandlerTest extends TestCase
{
    #[DataProvider('exceptionsProvider')]
    public function testGetStatusCode(\Throwable $exception, $expected)
    {
        $exceptionHandler = new ExceptionHandler();
        $this->assertEquals($expected['code'], $exceptionHandler->getStatusCode($exception));
    }

    public static function exceptionsProvider(): iterable
    {
        yield 'HttpExceptionInterface' => [
            'exception' => new HttpExceptionStub(123, ['header1' => 'value1']),
            'expected' => [
                'code' => 123,
                'headers' => [
                    'header1' => 'value1',
                ],
                'body' => [
                    'message' => 'HttpExceptionInterface',
                    'code' => 123,
                ],
            ],
        ];
        yield 'HttpAwareExceptionInterface' => [
            'exception' => new HttpAwareExceptionStub(234, ['header2' => 'value2']),
            'expected' => [
                'code' => 234,
                'headers' => [
                    'header2' => 'value2',
                ],
                'body' => [
                    'message' => 'HttpAwareExceptionInterface',
                    'code' => 234,
                ],
            ],
        ];
        yield 'Exception' => [
            'exception' => new \Exception(message: 'test message', code: 345),
            'expected' => [
                'code' => 500,
                'headers' => [],
                'body' => [
                    'message' => 'test message',
                    'code' => 500,
                ],
            ],
        ];
    }

    public function testSupports()
    {
        $exceptionHandler = new ExceptionHandler();
        $this->assertEquals(\Throwable::class, $exceptionHandler->supports());
    }

    #[DataProvider('exceptionsProvider')]
    public function testGetHeaders(\Throwable $exception, array $expected)
    {
        $exceptionHandler = new ExceptionHandler();
        $this->assertEquals($expected['headers'], $exceptionHandler->getHeaders($exception));
    }

    #[DataProvider('getBodyProvider')]
    public function testGetBodyWithoutDebugMode(\Throwable $exception, array $expected)
    {
        $exceptionHandler = new ExceptionHandler();
        $this->assertEquals($expected['body'], $exceptionHandler->getBody($exception));
    }

    public static function getBodyProvider(): iterable
    {
        yield 'forbidden with message' => [
            'exception' => new HttpExceptionStub(403, ['header1' => 'value1'], 'HttpExceptionStub'),
            'expected' => [
                'code' => 403,
                'headers' => [
                    'header1' => 'value1',
                ],
                'body' => [
                    'message' => 'HttpExceptionStub',
                    'code' => 403,
                ],
            ],
        ];
        yield 'forbidden with empty message' => [
            'exception' => new HttpExceptionStub(403, ['header1' => 'value1']),
            'expected' => [
                'code' => 403,
                'headers' => [
                    'header1' => 'value1',
                ],
                'body' => [
                    'message' => 'Access denied.',
                    'code' => 403,
                ],
            ],
        ];
        yield 'internal with empty message' => [
            'exception' => new HttpExceptionStub(501, ['header1' => 'value1']),
            'expected' => [
                'code' => 501,
                'headers' => [
                    'header1' => 'value1',
                ],
                'body' => [
                    'message' => 'Sorry, something went wrong. We cannot complete this operation at this time.',
                    'code' => 501,
                ],
            ],
        ];
    }

    #[DataProvider('getBodyProviderWithDebugProvider')]
    public function testGetBodyWitDebugMode(\Throwable $exception, array $expected)
    {
        $exceptionHandler = new ExceptionHandler(true);
        $this->assertEquals($expected['body'], $exceptionHandler->getBody($exception));
    }

    public static function getBodyProviderWithDebugProvider(): iterable
    {
        yield 'forbidden with message' => [
            'exception' => new HttpExceptionStub(403, ['header1' => 'value1'], 'Test message'),
            'expected' => [
                'code' => 403,
                'headers' => [
                    'header1' => 'value1',
                ],
                'body' => [
                    'message' => 'Test message',
                    'code' => 403,
                    'exception' => [
                        'message' => 'Test message',
                        'class' => HttpExceptionStub::class,
                        'file' => 'filename.php',
                        'line' => 300,
                        'trace' => [],
                    ],
                ],
            ],
        ];
        yield 'forbidden with empty message' => [
            'exception' => new HttpExceptionStub(403, ['header1' => 'value1'], ''),
            'expected' => [
                'code' => 403,
                'headers' => [
                    'header1' => 'value1',
                ],
                'body' => [
                    'message' => 'Access denied.',
                    'code' => 403,
                    'exception' => [
                        'message' => '',
                        'class' => HttpExceptionStub::class,
                        'file' => 'filename.php',
                        'line' => 300,
                        'trace' => [],
                    ],
                ],
            ],
        ];
        yield 'internal with empty message && ApiCallExceptionInterface' => [
            'exception' => new ApiCallExceptionStub('Test message', 503),
            'expected' => [
                'code' => 500,
                'headers' => [
                    'header1' => 'value1',
                ],
                'body' => [
                    'message' => 'Test message',
                    'code' => 500,
                    'exception' => [
                        'message' => 'Test message',
                        'class' => ApiCallExceptionStub::class,
                        'file' => 'filename.php',
                        'line' => 300,
                        'trace' => [],
                    ],
                    'response' => ['field1' => 'value1'],
                ],
            ],
        ];

        yield 'internal with empty message' => [
            'exception' => new HttpExceptionStub(501, ['header1' => 'value1'], ''),

            'expected' => [
                'code' => 501,
                'headers' => [
                    'header1' => 'value1',
                ],
                'body' => [
                    'message' => 'Sorry, something went wrong. We cannot complete this operation at this time.',
                    'code' => 501,
                    'exception' => [
                        'message' => '',
                        'class' => HttpExceptionStub::class,
                        'file' => 'filename.php',
                        'line' => 300,
                        'trace' => [],
                    ],
                ],
            ],
        ];
    }
}
