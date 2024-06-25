<?php
namespace Partitech\SonataExtra\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Sonata\AdminBundle\Admin\Pool;

class PreserveListParametersSubscriber implements EventSubscriberInterface
{

    private const SESSION_KEY = 'admin.list.parameters';
    private RequestStack $requestStack;
    private Pool $adminPool;

    public function __construct(RequestStack $requestStack, Pool $adminPool)
    {
        $this->requestStack = $requestStack;
        $this->adminPool = $adminPool;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $adminCode = $request->attributes->get('_sonata_admin');

        if ($adminCode && str_starts_with($request->attributes->get('_route'), 'admin_')) {
            $admin = $this->adminPool->getAdminByAdminCode($adminCode);

            $session = $this->requestStack->getSession();
            $sessionKey = self::SESSION_KEY . '.' . $adminCode;

            if(!empty($admin->preserveFilters)){

                if ($request->query->get('clear_filters') === 'true') {
                    $session->remove($sessionKey);
                    $request->query->remove('clear_filters');
                } else {
                    $parameters = $request->query->all();
                    unset($parameters['_page'], $parameters['_sort_by'], $parameters['_sort_order']);

                    if (!empty($parameters)) {
                        $session->set($sessionKey, $parameters);
                    } else {
                        $restoredParameters = $session->get($sessionKey, []);
                        if (!empty($restoredParameters)) {
                            $request->query->add($restoredParameters);
                        }
                    }
                }
            }

        }
    }


}
