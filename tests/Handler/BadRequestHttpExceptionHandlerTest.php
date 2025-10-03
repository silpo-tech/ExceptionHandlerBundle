<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Tests\Handler;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ExceptionHandlerBundle\Handler\BadRequestHttpExceptionHandler;
use ExceptionHandlerBundle\ValidationHandlerMessages;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class BadRequestHttpExceptionHandlerTest extends TestCase
{
    #[DataProvider('getBodyProvider')]
    public function testGetBody(BadRequestHttpExceptionHandler $exceptionHandler, \Throwable $exception, array $expected)
    {
        $this->assertEquals($expected['body'], $exceptionHandler->getBody($exception));
    }

    public static function getBodyProvider(): iterable
    {
        yield 'empty exception' => [
            'exceptionHandler' => new BadRequestHttpExceptionHandler(),
            'exception' => new BadRequestHttpException(
                previous: new ValidationFailedException(
                    'value',
                    new ConstraintViolationList(),
                ),
            ),
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => '',
                    'errors' => [],
                ],
            ],
        ];
        yield 'with custom message' => [
            'exceptionHandler' => new BadRequestHttpExceptionHandler(),
            'exception' => new BadRequestHttpException(
                message: 'Custom message',
                previous: new ValidationFailedException('value', new ConstraintViolationList()),
            ),
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => 'Custom message',
                    'errors' => [],
                ],
            ],
        ];
        yield 'with constraints' => [
            'exceptionHandler' => new BadRequestHttpExceptionHandler(),
            'exception' => new BadRequestHttpException(
                previous: new ValidationFailedException(
                    'value',
                    new ConstraintViolationList([
                        new ConstraintViolation(
                            message: 'Constraint message',
                            messageTemplate: null,
                            parameters: ['par' => 'value'],
                            root: null,
                            propertyPath: 'object.field',
                            invalidValue: ['par' => 'new'],
                        ),
                    ]),
                ),
            ),
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => '',
                    'errors' => [
                        'object.field' => ['Constraint message'],
                    ],
                ],
            ],
        ];
        yield 'without snakeCase decorating' => [
            'exceptionHandler' => new BadRequestHttpExceptionHandler(snakeCase: false),
            'exception' => new BadRequestHttpException(
                previous: new ValidationFailedException(
                    'value',
                    new ConstraintViolationList([
                        new ConstraintViolation(
                            message: 'Constraint message',
                            messageTemplate: null,
                            parameters: ['par' => 'value'],
                            root: null,
                            propertyPath: 'object_field_in_snake_case',
                            invalidValue: ['par' => 'new'],
                        ),
                    ]),
                ),
            ),
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => '',
                    'errors' => [
                        'objectFieldInSnakeCase' => ['Constraint message'],
                    ],
                ],
            ],
        ];
        yield 'with custom error key' => [
            'exceptionHandler' => new BadRequestHttpExceptionHandler(),
            'exception' => new BadRequestHttpException(
                previous: new ValidationFailedException(
                    'value',
                    new ConstraintViolationList([
                        new ConstraintViolation(
                            message: 'Constraint message',
                            messageTemplate: null,
                            parameters: ['par' => 'value'],
                            root: null,
                            propertyPath: 'HelloWorld',
                            invalidValue: ['par' => 'new'],
                        ),
                    ]),
                ),
            ),
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => '',
                    'errors' => [
                        '_hello_world' => ['Constraint message'],
                    ],
                ],
            ],
        ];
        yield 'with messageMap' => [
            'exceptionHandler' => new BadRequestHttpExceptionHandler(messagesMap: ValidationHandlerMessages::ERROR_MAP),
            'exception' => new BadRequestHttpException(
                previous: new ValidationFailedException(
                    'value',
                    new ConstraintViolationList([
                        new ConstraintViolation(
                            message: 'Constraint message',
                            messageTemplate: null,
                            parameters: ['par' => 'value'],
                            root: null,
                            propertyPath: 'object.field',
                            invalidValue: ['par' => 'new'],
                            code: NotBlank::IS_BLANK_ERROR,
                        ),
                    ]),
                ),
            ),
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => '',
                    'errors' => [
                        'object.field' => [ValidationHandlerMessages::ERROR_MAP[NotBlank::IS_BLANK_ERROR]],
                    ],
                ],
            ],
        ];
    }

    public function testSupports()
    {
        $handler = new BadRequestHttpExceptionHandler();
        $this->assertEquals(BadRequestHttpException::class, $handler->supports());
    }
}
