<?php

declare(strict_types=1);

namespace ExceptionHandlerBundle\DependencyInjection\Compiler;

use ExceptionHandlerBundle\Handler\ExceptionHandlerResolver;
use ExceptionHandlerBundle\Handler\MapperExceptionHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Finds all tagged services with tag "exception_handler" and registers it as exception-handler.
 */
class ExceptionHandlerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container): void
    {
        $resolver = $container->getDefinition(ExceptionHandlerResolver::class);

        $this->registerCustomHandlers($container);

        $handlers = [];

        foreach ($container->findTaggedServiceIds('exception_handler') as $id => $tags) {
            $handlers[$id] = $tags[0]['priority'] ?? 0;
        }

        uasort(
            $handlers,
            static fn ($priority1, $priority2) => $priority2 <=> $priority1,
        );
        // add to scenario calculator
        foreach (array_keys($handlers) as $handlerId) {
            $resolver->addMethodCall('addExceptionHandler', [$container->getDefinition($handlerId)]);
        }
    }

    private function registerCustomHandlers(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (array_key_exists('AutoMapperPlusBundle', $bundles)) {
            $container->register(MapperExceptionHandler::class, MapperExceptionHandler::class)
                ->setArgument('$debug', $container->getParameter('kernel.debug'))
                ->addTag('exception_handler', ['priority' => 100])
            ;
        }
    }
}
