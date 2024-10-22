<?php

namespace Partitech\SonataExtra\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Partitech\SonataExtra\Entity\Redirection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RedirectionListener
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (str_starts_with($request->getPathInfo(), '/_profiler') || str_starts_with($request->getPathInfo(), '/_wdt')) {
            return;
        }
        $repository = $this->entityManager->getRepository(Redirection::class);
        $redirection = $repository->findOneBy(['source' => $request->getPathInfo()]);

        if ($redirection && $redirection->getEnabled()) {
            $event->setResponse(new RedirectResponse($redirection->getTarget(), $redirection->getStatusCode()));
        }
    }
}
