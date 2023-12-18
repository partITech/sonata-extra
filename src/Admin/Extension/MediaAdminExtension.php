<?php

namespace Partitech\SonataExtra\Admin\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Provider\ImageProvider;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\Service\Attribute\Required;

// use Sonata\AdminBundle\Form\ListMapper;

class MediaAdminExtension extends AbstractAdminExtension
{
    private $entityManager;
    private $mediaManager;
    private $providerImage;
    private ParameterBagInterface $parameterBag;

    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        MediaManager $mediaManager,
        ImageProvider $providerImage
    ): void {
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->mediaManager = $mediaManager;
        $this->providerImage = $providerImage;
    }

    public function configure(AdminInterface $admin): void
    {
        $admin->setTemplates([
            'edit' => '@PartitechSonataExtra/Admin/media/custom_edit.html.twig',
        ]);
    }

}
