<?php

namespace Partitech\SonataExtra\DependencyInjection;

use Partitech\SonataExtra\DependencyInjection\Compiler\ResolveAsAdminAttributes;
use Sonata\Doctrine\Mapper\DoctrineCollector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Yaml\Tag\TaggedValue;

class PartitechSonataExtraExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Définir les paramètres du bundle comme des paramètres de service
        $container->setParameter('partitech_sonata_extra.smart_service', $config['smart_service']);
        $container->setParameter('partitech_sonata_extra.smart_service.provider.seo', $config['smart_service']['seo_provider']);
        $container->setParameter('partitech_sonata_extra.smart_service.provider.translation', $config['smart_service']['translation_provider']);
        $container->setParameter('partitech_sonata_extra.blog', $config['blog']);
        $container->setParameter('partitech_sonata_extra.page.realtime_preview', $config['page']['realtime_preview']);
        $container->setParameter('partitech_sonata_extra.content_security_policy', $config['content_security_policy']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');
        (new ResolveAsAdminAttributes($container))->registerAsAdminAttributes();

        $tagClass = $container->getParameter('sonata.classification.admin.tag.entity');
        $collector = DoctrineCollector::getInstance();
        $collector->addUnique($tagClass, 'tag_context', ['slug', 'context','site_id']);
    }

    private function resolveServices(mixed $value, string $file, bool $isParameter = false): mixed
    {
        if ($value instanceof TaggedValue) {
            $argument = $value->getValue();

            if ('closure' === $value->getTag()) {
                $argument = $this->resolveServices($argument, $file, $isParameter);

                return (new Definition('Closure'))
                    ->setFactory(['Closure', 'fromCallable'])
                    ->addArgument($argument);
            }
            if ('iterator' === $value->getTag()) {
                if (!\is_array($argument)) {
                    throw new InvalidArgumentException(sprintf('"!iterator" tag only accepts sequences in "%s".', $file));
                }
                $argument = $this->resolveServices($argument, $file, $isParameter);

                return new IteratorArgument($argument);
            }
            if ('service_closure' === $value->getTag()) {
                $argument = $this->resolveServices($argument, $file, $isParameter);

                return new ServiceClosureArgument($argument);
            }
            if ('service_locator' === $value->getTag()) {
                if (!\is_array($argument)) {
                    throw new InvalidArgumentException(sprintf('"!service_locator" tag only accepts maps in "%s".', $file));
                }

                $argument = $this->resolveServices($argument, $file, $isParameter);

                if (isset($argument[0])) {
                    trigger_deprecation('symfony/dependency-injection', '6.3', 'Using integers as keys in a "!service_locator" tag is deprecated. The keys will default to the IDs of the original services in 7.0.');
                }

                return new ServiceLocatorArgument($argument);
            }
            if (\in_array($value->getTag(), ['tagged', 'tagged_iterator', 'tagged_locator'], true)) {
                $forLocator = 'tagged_locator' === $value->getTag();

                if (\is_array($argument) && isset($argument['tag']) && $argument['tag']) {
                    if ($diff = array_diff(array_keys($argument), $supportedKeys = ['tag', 'index_by', 'default_index_method', 'default_priority_method', 'exclude', 'exclude_self'])) {
                        throw new InvalidArgumentException(sprintf('"!%s" tag contains unsupported key "%s"; supported ones are "%s".', $value->getTag(), implode('", "', $diff), implode('", "', $supportedKeys)));
                    }

                    $argument = new TaggedIteratorArgument($argument['tag'], $argument['index_by'] ?? null, $argument['default_index_method'] ?? null, $forLocator, $argument['default_priority_method'] ?? null, (array) ($argument['exclude'] ?? null), $argument['exclude_self'] ?? true);
                } elseif (\is_string($argument) && $argument) {
                    $argument = new TaggedIteratorArgument($argument, null, null, $forLocator);
                } else {
                    throw new InvalidArgumentException(sprintf('"!%s" tags only accept a non empty string or an array with a key "tag" in "%s".', $value->getTag(), $file));
                }

                if ($forLocator) {
                    $argument = new ServiceLocatorArgument($argument);
                }

                return $argument;
            }
            if ('service' === $value->getTag()) {
                if ($isParameter) {
                    throw new InvalidArgumentException(sprintf('Using an anonymous service in a parameter is not allowed in "%s".', $file));
                }

                $isLoadingInstanceof = $this->isLoadingInstanceof;
                $this->isLoadingInstanceof = false;
                $instanceof = $this->instanceof;
                $this->instanceof = [];

                $id = sprintf('.%d_%s', ++$this->anonymousServicesCount, preg_replace('/^.*\\\\/', '', $argument['class'] ?? '').$this->anonymousServicesSuffix);
                $this->parseDefinition($id, $argument, $file, []);

                if (!$this->container->hasDefinition($id)) {
                    throw new InvalidArgumentException(sprintf('Creating an alias using the tag "!service" is not allowed in "%s".', $file));
                }

                $this->container->getDefinition($id);

                $this->isLoadingInstanceof = $isLoadingInstanceof;
                $this->instanceof = $instanceof;

                return new Reference($id);
            }
            if ('abstract' === $value->getTag()) {
                return new AbstractArgument($value->getValue());
            }

            throw new InvalidArgumentException(sprintf('Unsupported tag "!%s".', $value->getTag()));
        }

        if (\is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->resolveServices($v, $file, $isParameter);
            }
        } elseif (\is_string($value) && str_starts_with($value, '@=')) {
            if ($isParameter) {
                throw new InvalidArgumentException(sprintf('Using expressions in parameters is not allowed in "%s".', $file));
            }

            if (!class_exists(Expression::class)) {
                throw new \LogicException('The "@=" expression syntax cannot be used without the ExpressionLanguage component. Try running "composer require symfony/expression-language".');
            }

            return new Expression(substr($value, 2));
        } elseif (\is_string($value) && str_starts_with($value, '@')) {
            if (str_starts_with($value, '@@')) {
                $value = substr($value, 1);
                $invalidBehavior = null;
            } elseif (str_starts_with($value, '@!')) {
                $value = substr($value, 2);
                $invalidBehavior = ContainerInterface::IGNORE_ON_UNINITIALIZED_REFERENCE;
            } elseif (str_starts_with($value, '@?')) {
                $value = substr($value, 2);
                $invalidBehavior = ContainerInterface::IGNORE_ON_INVALID_REFERENCE;
            } else {
                $value = substr($value, 1);
                $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE;
            }

            if (null !== $invalidBehavior) {
                $value = new Reference($value, $invalidBehavior);
            }
        }

        return $value;
    }
}
