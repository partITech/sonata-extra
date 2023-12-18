<?php

namespace Partitech\SonataExtra\SmartService;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface SmartServiceProviderInterface
{
    public function translate(string $text, string $targetLanguage): string;

    public function translateArray(array $arrayOfText, string $targetLanguage): array;

    public function setLogger(LoggerInterface $logger):void;

    public function setContainer(ContainerInterface $container):void;

}
