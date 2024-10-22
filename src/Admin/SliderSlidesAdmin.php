<?php

declare(strict_types=1);

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Partitech\SonataExtra\Entity\SliderSlides;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Provider\ImageProviderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Service\Attribute\Required;

#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'Slides',
    model_class: SliderSlides::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
final class SliderSlidesAdmin extends AbstractAdmin
{
    private ImageProviderInterface $providerImage;
    private MediaManager $mediaManager;

    private ParameterBagInterface $parameterBag;

    #[Required]
    public function required(
        ParameterBagInterface $parameterBag,
    ): void {
        $this->parameterBag = $parameterBag;
    }

    #[Required]
    public function setProviderImage(ImageProvider $providerImage): self
    {
        $this->providerImage = $providerImage;

        return $this;
    }

    #[Required]
    public function setMediaManager(MediaManager $mediaManager): self
    {
        $this->mediaManager = $mediaManager;

        return $this;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, ['label' => 'Titre'])
            ->add('subtitle', null, ['label' => 'Sous titre'])
            ->add('btn_label', null, ['label' => 'Label du bouton'])
            ->add('url')
            ->add('target')
            ->add('ordre')
            ->add('active')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('title')
            ->add('subtitle', null, ['label' => 'Sous titre'])
            ->add('btn_label', null, ['label' => 'Label du bouton'])
            ->add('target')
            ->add('ordre')
            ->add('active')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $imageHtml=null;

        if ($this->hasSubject() && null !== $this->getSubject()->getMediaMedia()) {
            $media = $this->mediaManager->findOneBy([
                'id' => $this->getSubject()->getMediaMedia()->getId(),
            ]);
            $mediaUrl = $this->providerImage->generatePublicUrl($media, 'default_small');
            $imageHtml = $mediaUrl ? '<img src="'.$mediaUrl.'" alt="'.$media->getName().'" class="img-thumbnail" />' : 'Aucune image sélectionnée';
        }

        $form
            ->add('id')
            ->add('active')

            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez renseigner une valeur',
                    ]),
                ],
            ])
            ->add('subtitle', null, ['label' => 'Sous titre'])
            ->add('btn_label', null, ['label' => 'Label du bouton'])
            ->add('mediaMedia', ModelListType::class, [
                'label' => 'Média',
                'required' => false,
                'btn_add' => true,
                'btn_edit' => true,
                'btn_list' => false,
                'btn_delete' => false,
                'class' => $this->parameterBag->get('partitech_sonata_extra.class.media'),
                'help' => $imageHtml,
                'help_html' => true,
            ],
                ['link_parameters' => [
                    'context' => 'default',
                    'provider' => 'sonata.media.provider.image',
                ]]
            )
            ->add('url')
            ->add('target', ChoiceType::class, [
                'label' => 'Target',
                'choices' => [
                    '_Self' => '_Self',
                    '_Blank' => '_Blank',
                ],
                'required' => false,
                'placeholder' => 'Aucune',
            ])
            ->add('ordre', HiddenType::class)
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('title')
            ->add('subtitle')
            ->add('btn_label')
            ->add('target')
            ->add('url')
            ->add('ordre')
        ;
    }
}
