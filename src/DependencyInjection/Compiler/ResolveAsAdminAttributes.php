<?php

namespace Partitech\SonataExtra\DependencyInjection\Compiler;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

readonly class ResolveAsAdminAttributes
{
    public function __construct(private ContainerBuilder $container)
    {
    }

    public function registerAsAdminAttributes(): void
    {
        $this->container->registerAttributeForAutoconfiguration(
            AsAdmin::class,
            function (ChildDefinition $definition, AsAdmin $asAdmin, \ReflectionClass $reflector): void {
                $definition->addTag('sonata.admin', $asAdmin->getTags());
                $definition->setClass($reflector->getName());
                $this->processMethodCalls($definition, $asAdmin);
            }
        );
    }

    private function processMethodCalls(ChildDefinition $definition, AsAdmin $asAdmin): void
    {
        foreach ($asAdmin->getCalls() as $call) {
            $methodName = $call[0];
            $methodArgs = $call[1];
            $resolvedArgs = $this->resolveMethodArguments($methodArgs);
            if (!empty($resolvedArgs)) {
                $definition->addMethodCall($methodName, $resolvedArgs);
            }
        }
    }

    private function resolveMethodArguments(array $methodArgs): array
    {
        $resolvedArgs = [];
        foreach ($methodArgs as $arg) {
            if (is_null($arg)) {
                continue;
            }

            if (is_string($arg) && ($this->container->has($arg) || class_exists($arg))) {
                $resolvedArgs[] = new Reference($arg);
            } elseif (is_array($arg)) {
                $resolvedArgs[] = $this->resolveArrayArguments($arg);
            } else {
                $resolvedArgs[] = $arg;
            }
        }

        return $resolvedArgs;
    }

    private function resolveArrayArguments(array $args): array
    {
        $resolvedArgs = [];
        foreach ($args as $arg) {
            $resolvedArgs[] = ($this->container->has($arg) || class_exists($arg)) ? new Reference($arg) : $arg;
        }

        return $resolvedArgs;
    }
}
