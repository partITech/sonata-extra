<?php

    namespace Partitech\SonataExtra\Routing;
    
    use App\Entity\SonataPagePage;
    use Doctrine\ORM\EntityManagerInterface;
    use Sonata\PageBundle\Site\SiteSelectorInterface;
    use Symfony\Component\Config\Loader\Loader;
    use Symfony\Component\Routing\Route;
    use Symfony\Component\Routing\RouteCollection;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
    use Symfony\Component\Config\Loader\LoaderInterface;
    use Symfony\Component\HttpKernel\KernelInterface;
    
    class DatabaseRouteLoader extends Loader
    {
        private $isLoaded = false;
        private SiteSelectorInterface $siteSelector;
        private $kernel;
        
        public function __construct(
            private EntityManagerInterface $entityManager,
            RouterInterface $router,
            SiteSelectorInterface $siteSelector,
            ContainerInterface $container,
            KernelInterface $kernel
        
        ) {
            $this->router = $router;
            $this->siteSelector = $siteSelector;
            $this->container = $container;
            $this->kernel = $kernel;
        }
        
        
        public function load($resource, string $type = null): RouteCollection
        {
            
            if ($this->isLoaded) {
                throw new \RuntimeException('Do not add the "database_routes" loader twice');
            }
            
            $routeCollection = new RouteCollection();
            //$currentSite = $this->siteSelector->retrieve();


            $query = $this->entityManager->getRepository(SonataPagePage::class)->createQueryBuilder('p')
                                         ->where('p.customUrl IS NOT NULL')
                                    ->andWhere('p.routeName !=:page_slug ')
                                    ->andWhere('p.routeName !=:_page_internal_global ')
                                         ->andWhere('p.customUrl != :customUrl ')

                                         ->setParameter('_page_internal_global', '_page_internal_global')
                                         ->setParameter('page_slug', 'page_slug')
                                         ->setParameter('customUrl', '')
                                         //->groupBy('p.customUrl')
                                         ->getQuery();

            $pages = $query->getResult();

            $all_routes=$this->getAllRoutes();

            foreach ($pages as $page) {

                $route_name = $page->getRouteName();
                $route_alias_name=preg_replace('/^_page_alias_/', '', $page->getPageAlias());

                if (!empty($all_routes[$route_alias_name])) {

                    $controller = $this->getControllerFromArray($all_routes[$route_alias_name]);
                    $newRoute = new Route($page->getCustomUrl(), [
                        '_controller' => $controller
                    ]);
                    $routeCollection->add($route_name, $newRoute);



                }


            }

        $this->isLoaded = true;

        return $routeCollection;
    }


    public function getAllRoutes(): array
    {
        $cacheDir = $this->kernel->getCacheDir();
        $cacheRoutePath = $cacheDir . '/url_generating_routes.php';
        if (file_exists($cacheRoutePath)) {
            $routesArray = include $cacheRoutePath;
            return $routesArray;
        } else {
            return [];
        }
    }

    function getControllerFromArray($routeArray) {
        foreach ($routeArray as $element) {
            if (is_array($element) && isset($element['_controller'])) {
                return $element['_controller'];
            }
        }
        return null;
    }


    public function supports($resource, $type = null): bool
    {
        return 'partitech_db_page_route' === $type;
    }

}