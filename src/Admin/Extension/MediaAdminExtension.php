<?php

namespace Partitech\SonataExtra\Admin\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Provider\ImageProvider;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;

class MediaAdminExtension extends AbstractAdminExtension
{
    private EntityManagerInterface $entityManager;
    private MediaManager $mediaManager;
    private ImageProvider $providerImage;
    private ParameterBagInterface $parameterBag;

    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        ParameterBagInterface  $parameterBag,
        MediaManager           $mediaManager,
        ImageProvider          $providerImage
    ): void
    {
        $this->entityManager = $entityManager;
        $this->parameterBag  = $parameterBag;
        $this->mediaManager  = $mediaManager;
        $this->providerImage = $providerImage;
    }

    public function configure(AdminInterface $admin): void
    {
        $admin->setTemplates([
            'edit' => '@PartitechSonataExtra/Admin/media/custom_edit.html.twig',
        ]);
    }

}
