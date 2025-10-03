<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Tests\Handler;

use AutoMapperPlus\Exception\UnsupportedSourceTypeException;
use ExceptionHandlerBundle\ExceptionHandlerMessages;
use ExceptionHandlerBundle\Handler\MapperExceptionHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MapperExceptionHandlerTest extends TestCase
{
    public function testGetStatusCode()
    {
        $exception = UnsupportedSourceTypeException::fromType('string');
        $handler = new MapperExceptionHandler();
        $this->assertEquals(400, $handler->getStatusCode($exception));
    }

    public function testSupports()
    {
        $handler = new MapperExceptionHandler();
        $this->assertEquals(UnsupportedSourceTypeException::class, $handler->supports());
    }

    #[DataProvider('providerGetBody')]
    public function testGetBody(
        MapperExceptionHandler $exceptionHandler,
        UnsupportedSourceTypeException $exception,
        array $expected,
    ) {
        $this->assertEquals($expected, $exceptionHandler->getBody($exception));
    }

    public static function providerGetBody(): iterable
    {
        yield 'default exception' => [
            'exceptionHandler' => new MapperExceptionHandler(),
            'exception' => UnsupportedSourceTypeException::fromType('string'),
            'expected' => [
                'code' => 400,
                'message' => ExceptionHandlerMessages::VALIDATION_ERROR,
                'errors' => [UnsupportedSourceTypeException::fromType('string')->getMessage()],
            ],
        ];
    }
}
