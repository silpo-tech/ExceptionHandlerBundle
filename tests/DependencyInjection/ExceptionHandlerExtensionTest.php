<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SilpoTech\ExceptionHandlerBundle\DependencyInjection\ExceptionHandlerExtension;
use SilpoTech\ExceptionHandlerBundle\EventListener\ExceptionListener;
use SilpoTech\ExceptionHandlerBundle\Handler\BadRequestHttpExceptionHandler;
use SilpoTech\ExceptionHandlerBundle\Handler\ExceptionHandler;
use SilpoTech\ExceptionHandlerBundle\Handler\FormValidationHandler;
use SilpoTech\ExceptionHandlerBundle\Handler\ValidationHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExceptionHandlerExtensionTest extends TestCase
{
    public static function providerLoad(): iterable
    {
        yield 'default' => [
            'expected' => [
                'parameters' => [
                    'validation_messages_map' => [],
                    'validation_snake_case' => true,
                ],
                'services' => [
                    ExceptionListener::class,
                    ExceptionHandler::class,
                    FormValidationHandler::class,
                    ValidationHandler::class,
                    BadRequestHttpExceptionHandler::class,
                ],
                'excluded' => [
                    'SilpoTech\ExceptionHandlerBundle\DependencyInjection',
                    'SilpoTech\ExceptionHandlerBundle\Exception',
                    'SilpoTech\ExceptionHandlerBundle\Handler\MapperExceptionHandler',
                ],
            ],
        ];
    }

    #[DataProvider('providerLoad')]
    public function testLoad(array $expected)
    {
        $extension = new ExceptionHandlerExtension();
        $containerBuilder = new ContainerBuilder();
        $extension->load([], $containerBuilder);
        foreach ($expected['parameters'] as $key => $value) {
            $this->assertSame($value, $containerBuilder->getParameter($key));
        }
        foreach ($expected['services'] as $service) {
            $this->assertTrue($containerBuilder->hasDefinition($service));
        }
        foreach ($expected['excluded'] as $excluded) {
            if ($containerBuilder->hasDefinition($excluded)) {
                $definition = $containerBuilder->getDefinition($excluded);
                $this->assertSame([['source' => 'in "config/services.yml"']], $definition->getTag('container.excluded'));
            }
        }
    }
}
