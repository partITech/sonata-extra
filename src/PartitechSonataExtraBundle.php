<?php

namespace Partitech\SonataExtra;

use Partitech\SonataExtra\DependencyInjection\PartitechSonataExtraExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class PartitechSonataExtraBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new PartitechSonataExtraExtension();
    }

    /**
     * @return void
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                $definition = $container->findDefinition('doctrine.orm.resolve_target_entity_listener');

                $userClass = $container->getParameter('sonata.user.user.class');
                $categoryClass = $container->getParameter('sonata.classification.admin.category.entity');
                $tagClass = $container->getParameter('sonata.classification.admin.tag.entity');
                $mediaClass = $container->getParameter('sonata.media.media.class');
                $siteClass = $container->getParameter('sonata.page.site.class');

                $definition->addMethodCall('addResolveTargetEntity', ['Partitech\SonataExtra\Contract\UserInterface', $userClass, []]);
                $definition->addMethodCall('addResolveTargetEntity', ['Partitech\SonataExtra\Contract\CategoryInterface', $categoryClass, []]);
                $definition->addMethodCall('addResolveTargetEntity', ['Partitech\SonataExtra\Contract\TagInterface', $tagClass, []]);
                $definition->addMethodCall('addResolveTargetEntity', ['Partitech\SonataExtra\Contract\MediaInterface', $mediaClass, []]);
                $definition->addMethodCall('addResolveTargetEntity', ['Partitech\SonataExtra\Contract\SiteInterface', $siteClass, []]);

                $container->setParameter('partitech_sonata_extra.user.class', $userClass);
                $container->setParameter('partitech_sonata_extra.category.class', $categoryClass);
                $container->setParameter('partitech_sonata_extra.tag.class', $tagClass);
                $container->setParameter('partitech_sonata_extra.class.media', $mediaClass);
                $container->setParameter('partitech_sonata_extra.class.site', $siteClass);

            }
        });
    }
}
