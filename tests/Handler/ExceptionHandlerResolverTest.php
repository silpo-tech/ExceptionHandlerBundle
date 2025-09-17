<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Tests\Handler;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SilpoTech\ExceptionHandlerBundle\Exception\ValidationException;
use SilpoTech\ExceptionHandlerBundle\Handler\BadRequestHttpExceptionHandler;
use SilpoTech\ExceptionHandlerBundle\Handler\ExceptionHandler;
use SilpoTech\ExceptionHandlerBundle\Handler\ExceptionHandlerInterface;
use SilpoTech\ExceptionHandlerBundle\Handler\ExceptionHandlerResolver;
use SilpoTech\ExceptionHandlerBundle\Handler\FormValidationHandler;
use SilpoTech\ExceptionHandlerBundle\Handler\ValidationHandler;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ExceptionHandlerResolverTest extends TestCase
{
    public function testAddExceptionHandler()
    {
        $exceptionHandlerResolver = new ExceptionHandlerResolver();
        $formValidationHandler = new FormValidationHandler();
        $exceptionHandlerResolver->addExceptionHandler($formValidationHandler);

        $reflection = new \ReflectionObject($exceptionHandlerResolver);
        $property = $reflection->getProperty('handlers');
        $values = $property->getValue($exceptionHandlerResolver);

        $this->assertCount(1, $values);
        $this->assertEquals($formValidationHandler, $values[0]);
    }

    #[DataProvider('providerResolve')]
    public function testResolve(array $handlers, \Throwable $throwable, ?ExceptionHandlerInterface $expected)
    {
        $exceptionHandlerResolver = new ExceptionHandlerResolver();
        foreach ($handlers as $handler) {
            $exceptionHandlerResolver->addExceptionHandler($handler);
        }

        $this->assertEquals($exceptionHandlerResolver->resolve($throwable), $expected);
    }

    public static function providerResolve(): iterable
    {
        yield 'BadRequestHttpExceptionHandler' => [
            'handlers' => [
                new BadRequestHttpExceptionHandler(),
                new ValidationHandler(),
                new ExceptionHandler(),
            ],
            'throwable' => new BadRequestHttpException(),
            'expected' => new BadRequestHttpExceptionHandler(),
        ];
        yield 'ValidationHandler' => [
            'handlers' => [
                new BadRequestHttpExceptionHandler(),
                new ValidationHandler(),
                new ExceptionHandler(),
            ],
            'throwable' => new ValidationException([]),
            'expected' => new ValidationHandler(),
        ];
        yield 'ExceptionHandler' => [
            'handlers' => [
                new BadRequestHttpExceptionHandler(),
                new ValidationHandler(),
                new ExceptionHandler(),
            ],
            'throwable' => new \Exception(),
            'expected' => new ExceptionHandler(),
        ];
        yield 'handler not found' => [
            'handlers' => [
                new BadRequestHttpExceptionHandler(),
            ],
            'throwable' => new \Exception(),
            'expected' => null,
        ];
        yield 'ValidationHandler not initialized, used ExceptionHandler' => [
            'handlers' => [
                new ExceptionHandler(),
            ],
            'throwable' => new ValidationException([]),
            'expected' => new ExceptionHandler(),
        ];
    }
}
