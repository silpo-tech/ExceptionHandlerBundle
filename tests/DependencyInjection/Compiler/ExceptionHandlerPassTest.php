<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\Tests\DependencyInjection\Compiler;

use ExceptionHandlerBundle\DependencyInjection\Compiler\ExceptionHandlerPass;
use ExceptionHandlerBundle\Handler\BadRequestHttpExceptionHandler;
use ExceptionHandlerBundle\Handler\ExceptionHandler;
use ExceptionHandlerBundle\Handler\ExceptionHandlerResolver;
use ExceptionHandlerBundle\Handler\FormValidationHandler;
use ExceptionHandlerBundle\Handler\MapperExceptionHandler;
use ExceptionHandlerBundle\Handler\ValidationHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExceptionHandlerPassTest extends TestCase
{
    #[DataProvider('providerProcess')]
    public function testProcess(array $diHandlers, array $bundles, array $expected)
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->register(ExceptionHandlerResolver::class);
        foreach ($diHandlers as $diHandler) {
            $containerBuilder->register($diHandler['class'], $diHandler['class'])
                ->addTag('exception_handler', ['priority' => $diHandler['priority']])
            ;
        }
        $containerBuilder->setParameter('kernel.debug', true);
        $containerBuilder->setParameter('kernel.bundles', $bundles);

        (new ExceptionHandlerPass())->process($containerBuilder);

        $definition = $containerBuilder->getDefinition(ExceptionHandlerResolver::class);
        $this->assertCount($expected['count'], $definition->getMethodCalls());
        foreach ($definition->getMethodCalls() as $i => $method) {
            $def = $method[1][0]->getClass();
            $this->assertEquals($expected['order'][$i], $def);
        }
    }

    public static function providerProcess(): iterable
    {
        yield 'default' => [
            'diHandlers' => [
                [
                    'class' => ExceptionHandler::class,
                    'priority' => -100,
                ],
                [
                    'class' => FormValidationHandler::class,
                    'priority' => 100,
                ],
                [
                    'class' => ValidationHandler::class,
                    'priority' => 100,
                ],
                [
                    'class' => BadRequestHttpExceptionHandler::class,
                    'priority' => 100,
                ],
            ],
            'bundles' => [],
            'expected' => [
                'count' => 4,
                'order' => [
                    FormValidationHandler::class,
                    ValidationHandler::class,
                    BadRequestHttpExceptionHandler::class,
                    ExceptionHandler::class,
                ],
            ],
        ];
        yield 'custom sorts' => [
            'diHandlers' => [
                [
                    'class' => ExceptionHandler::class,
                    'priority' => 3,
                ],
                [
                    'class' => FormValidationHandler::class,
                    'priority' => 1,
                ],
                [
                    'class' => ValidationHandler::class,
                    'priority' => 4,
                ],
                [
                    'class' => BadRequestHttpExceptionHandler::class,
                    'priority' => 2,
                ],
            ],
            'bundles' => [],
            'expected' => [
                'count' => 4,
                'order' => [
                    ValidationHandler::class,
                    ExceptionHandler::class,
                    BadRequestHttpExceptionHandler::class,
                    FormValidationHandler::class,
                ],
            ],
        ];
        yield 'with AutoMapperPlusBundle' => [
            'diHandlers' => [
                [
                    'class' => ExceptionHandler::class,
                    'priority' => -3,
                ],
                [
                    'class' => FormValidationHandler::class,
                    'priority' => -2,
                ],
                [
                    'class' => ValidationHandler::class,
                    'priority' => -1,
                ],
                [
                    'class' => BadRequestHttpExceptionHandler::class,
                    'priority' => 0,
                ],
            ],
            'bundles' => [
                'AutoMapperPlusBundle' => 'AutoMapperPlusBundle::class',
            ],
            'expected' => [
                'count' => 5,
                'order' => [
                    MapperExceptionHandler::class,
                    BadRequestHttpExceptionHandler::class,
                    ValidationHandler::class,
                    FormValidationHandler::class,
                    ExceptionHandler::class,
                ],
            ],
        ];
    }
}
