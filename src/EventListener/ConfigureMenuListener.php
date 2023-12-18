<?php

namespace Partitech\SonataExtra\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConfigureMenuListener
{
    private $entityManager;
    private $requestStack;
    private $security;
    private ParameterBagInterface $parameterBag;
    private TranslatorInterface $translator;

    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        RequestStack $requestStack,
        Security $security,
        ParameterBagInterface $parameterBag,
        TranslatorInterface $translator
    ): void {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->parameterBag = $parameterBag;
        $this->translator = $translator;
    }

    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        if (!$this->security->isGranted('ROLE_APPROVE')) {
            return;
        }
        $menu = $event->getMenu();
        $advancedGroup = $menu->getChild('advanced');

        if ($advancedGroup) {
            $count = $this->countUnapprovedActivities();
            $approvalLogItem = $advancedGroup->getChild('Modifications en attentes');
            if ($approvalLogItem) {
                $approvalLogItem->setExtras(['sup' => $count] + $approvalLogItem->getExtras());
            }

            $request = $this->requestStack->getCurrentRequest();
            $currentRoute = $request->attributes->get('_route');
            $prefixes = $this->parameterBag->get('sonata_approve_menu');

            $matchFound = false;
            foreach ($prefixes as $prefix) {
                if (0 === strpos($currentRoute, $prefix)) {
                    $matchFound = true;
                    break;
                }
            }

            if (!$matchFound && $count) {
                $menu->addChild('app.admin.admin_approval_log', [
                    'label' => 'Modifications en attentes',
                    'route' => 'admin_partitech_sonataextra_approval_list',
                    'extras' => ['sup' => $count],
                ]);
            }
        }
    }

    private function countUnapprovedActivities(): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(log.id)')
            ->from('Partitech\SonataExtra\Entity\AdminActivityLog', 'log')
            ->where('log.approval = 0');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
