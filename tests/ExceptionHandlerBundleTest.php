<?php

declare(strict_types=1);

namespace SilpoTech\ExceptionHandlerBundle\Tests;

use PHPUnit\Framework\TestCase;
use SilpoTech\ExceptionHandlerBundle\DependencyInjection\Compiler\ExceptionHandlerPass;
use SilpoTech\ExceptionHandlerBundle\ExceptionHandlerBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ExceptionHandlerBundleTest extends TestCase
{
    public function testBuild()
    {
        $containerBuilder = new ContainerBuilder();
        $bundle = new ExceptionHandlerBundle();
        $bundle->build($containerBuilder);
        $passes = $containerBuilder->getCompiler()->getPassConfig()->getBeforeOptimizationPasses();

        $this->assertContains(
            ExceptionHandlerPass::class,
            array_map(static fn (CompilerPassInterface $pass) => $pass::class, $passes),
        );
    }
}
