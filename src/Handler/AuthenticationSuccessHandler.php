<?php

namespace Partitech\SonataExtra\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Contracts\Service\Attribute\Required;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;

    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
    ): void {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
    }

    public function __construct(HttpUtils $httpUtils)
    {
        parent::__construct($httpUtils, []);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $siteClass = $this->parameterBag->get('sonata.page.site.class');
        $qb = $this->entityManager->createQueryBuilder();
        $defaultSite = $qb->select('s')
            ->from($siteClass, 's')
            ->where($qb->expr()->eq('s.isDefault', ':isDefault'))
            ->setParameter('isDefault', true)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $defaultSite) {
            throw $this->createNotFoundException('No default site found');
        }

        $url = sprintf(
            'http://%s%s',
            $defaultSite->getHost(),
            $defaultSite->getRelativePath()
        );

        // we should add / before the admin path.
        // if $defaultSite->getRelativePath() === '/'
        // we should not.
        if(substr($url, -1) !== '/'){
            $url .= '/';
        }

        $url .= 'admin';

        return new RedirectResponse($url);
    }
}
