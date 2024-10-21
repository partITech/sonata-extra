<?php

namespace Partitech\SonataExtra\SmartService;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SmartServiceProviderFactory implements SmartServiceProviderFactoryInterface
{
    private ParameterBagInterface $params;
    private ContainerInterface $container;
    private LoggerInterface $logger;

    #[Required]
    public function autowireDependencies(
        ParameterBagInterface $params,
        ContainerInterface $container,
        LoggerInterface $logger,
    ): void {
        $this->params = $params;
        $this->logger = $logger;
        $this->container = $container;
    }

    /**
     * @throws \Exception
     */
    public function create(string $providerName = null): SmartServiceProviderInterface
    {
        $config = $this->params->get('partitech_sonata_extra.smart_service');
        if (empty($providerName)) {
            $providerName = $config['default_provider'];
        }
        if (!empty($config['providers'][$providerName])) {
            if (!empty($config['providers'][$providerName]['class'])) {
                $provider = new $config['providers'][$providerName]['class']();
                $provider->setConfig($config['providers'][$providerName]);
                $provider->setLogger($this->logger);
                $provider->setContainer($this->container);

                return $provider;
            }
        } else {
            throw new \Exception("Smart service provider '{$providerName}' is not supported");
        }
    }
}
