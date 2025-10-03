<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Tests\Handler;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ExceptionHandlerBundle\Exception\ValidationException;
use ExceptionHandlerBundle\Exception\ValidationExceptionInterface;
use ExceptionHandlerBundle\ExceptionHandlerMessages;
use ExceptionHandlerBundle\Handler\ValidationHandler;
use ExceptionHandlerBundle\ValidationHandlerMessages;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;

class ValidationHandlerTest extends TestCase
{
    #[DataProvider('getBodyProvider')]
    public function testGetBody(array $exceptionHandlerArguments, array $exceptionArguments, array $expected)
    {
        $exceptionHandler = new ValidationHandler(
            $exceptionHandlerArguments['debug'] ?? false,
            $exceptionHandlerArguments['messagesMap'] ?? [],
            $exceptionHandlerArguments['snakeCase'] ?? true,
        );
        $exception = new ValidationException(
            $exceptionArguments['errors'] ?? [],
            $exceptionArguments['message'] ?? ExceptionHandlerMessages::VALIDATION_ERROR,
        );
        $this->assertEquals($expected['body'], $exceptionHandler->getBody($exception));
    }

    public static function getBodyProvider(): iterable
    {
        yield 'empty exception' => [
            'exceptionHandlerArguments' => [],
            'exceptionArguments' => [],
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => 'Sorry, something went wrong. We cannot complete this operation at this time. Code: Invalid request parameters.',
                    'errors' => [],
                ],
            ],
        ];
        yield 'with custom message' => [
            'exceptionHandlerArguments' => [],
            'exceptionArguments' => ['message' => 'Custom message'],
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => 'Custom message',
                    'errors' => [],
                ],
            ],
        ];
        yield 'with constraints' => [
            'exceptionHandlerArguments' => [],
            'exceptionArguments' => [
                'errors' => [
                    new ConstraintViolation(
                        message: 'Constraint message',
                        messageTemplate: null,
                        parameters: ['par' => 'value'],
                        root: null,
                        propertyPath: 'object.field',
                        invalidValue: ['par' => 'new'],
                    ),
                ],
            ],
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => 'Sorry, something went wrong. We cannot complete this operation at this time. Code: Invalid request parameters.',
                    'errors' => [
                        'object.field' => ['Constraint message'],
                    ],
                ],
            ],
        ];
        yield 'with array' => [
            'exceptionHandlerArguments' => [],
            'exceptionArguments' => [
                'errors' => [
                    'object.field' => ['message1', 'message2'],
                ],
            ],
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => 'Sorry, something went wrong. We cannot complete this operation at this time. Code: Invalid request parameters.',
                    'errors' => [
                        'object.field' => ['message1', 'message2'],
                    ],
                ],
            ],
        ];
        yield 'with default' => [
            'exceptionHandlerArguments' => [],
            'exceptionArguments' => [
                'errors' => [
                    'object.field' => 'value',
                ],
            ],
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => 'Sorry, something went wrong. We cannot complete this operation at this time. Code: Invalid request parameters.',
                    'errors' => [
                        'object.field' => ['value'],
                    ],
                ],
            ],
        ];
        yield 'without snakeCase decorating' => [
            'exceptionHandlerArguments' => ['snakeCase' => false],
            'exceptionArguments' => [
                'errors' => [
                    'object_field_in_snake_case' => 'value',
                ],
            ],
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => 'Sorry, something went wrong. We cannot complete this operation at this time. Code: Invalid request parameters.',
                    'errors' => [
                        'objectFieldInSnakeCase' => ['value'],
                    ],
                ],
            ],
        ];
        yield 'with custom error key' => [
            'exceptionHandlerArguments' => [],
            'exceptionArguments' => [
                'errors' => [
                    'HelloWorld' => 'value',
                ],
            ],
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => 'Sorry, something went wrong. We cannot complete this operation at this time. Code: Invalid request parameters.',
                    'errors' => [
                        '_hello_world' => ['value'],
                    ],
                ],
            ],
        ];
        yield 'with messagesMap' => [
            'exceptionHandlerArguments' => ['messagesMap' => ValidationHandlerMessages::ERROR_MAP],
            'exceptionArguments' => [
                'errors' => [
                    'object.field' => [NotBlank::IS_BLANK_ERROR],
                ],
            ],
            'expected' => [
                'body' => [
                    'code' => 400,
                    'message' => 'Sorry, something went wrong. We cannot complete this operation at this time. Code: Invalid request parameters.',
                    'errors' => [
                        'object.field' => [ValidationHandlerMessages::ERROR_MAP[NotBlank::IS_BLANK_ERROR]],
                    ],
                ],
            ],
        ];
    }

    public function testSupports()
    {
        $handler = new ValidationHandler();
        $this->assertEquals(ValidationExceptionInterface::class, $handler->supports());
    }
}
