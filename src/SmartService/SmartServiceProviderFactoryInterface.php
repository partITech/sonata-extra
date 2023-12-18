<?php

namespace Partitech\SonataExtra\SmartService;

interface SmartServiceProviderFactoryInterface
{
    public function create(string $providerName): SmartServiceProviderInterface;
}
