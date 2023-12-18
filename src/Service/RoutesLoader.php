<?php

namespace Partitech\SonataExtra\Service;

use Partitech\SonataExtra\Model\Route;
use Symfony\Component\Routing\RouterInterface;

class RoutesLoader
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getRoutes(): array
    {
        $routes = $this->router->getRouteCollection()->all();
        $sonataAdminRoutes = [];

        foreach ($routes as $routeName => $route) {
            if (str_starts_with($routeName, 'admin_')) {
                $sonataAdminRoutes[] = new Route($routeName, $route->getPath(), $route->getMethods());
            }
        }

        return $sonataAdminRoutes;
    }
}
