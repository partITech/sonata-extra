<?php

namespace Partitech\SonataExtra\Controller;

use Partitech\SonataExtra\Service\RoutesLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RoutesController extends AbstractController
{
    #[Route('/admin/sonata-extra/routes', name: 'sonata_extra_routes', methods: ['GET'])]
    public function list(RoutesLoader $routesLoader): Response
    {
        $routes = $routesLoader->getRoutes();

        return $this->render('@PartitechSonataExtra/Routes/list.html.twig', [
            'routes' => $routes,
        ]);
    }
}
