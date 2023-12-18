<?php

// src/Block/HelloWorldBlockService.php

namespace Partitech\SonataExtra\Block;

use Doctrine\ORM\EntityManagerInterface;
use Partitech\SonataExtra\Entity\Slider;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

#[AutoconfigureTag(name: 'sonata.block')]
final class SliderBlockService extends AbstractBlockService implements EditableBlockService
{
    private $entityManager;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager)
    {
        parent::__construct($twig);
        $this->entityManager = $entityManager;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        // $template = $blockContext->getTemplate();

        $settings = $blockContext->getSettings();
        $template = $settings['template'];

        $sliderId = $settings['slider'];

        $slider = $this->entityManager->getRepository(Slider::class)->find($sliderId);

        return $this->renderResponse($template, [
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
            'slider' => $slider,
        ], $response);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $sliders = $this->entityManager->getRepository(Slider::class)->findAll();

        $sliderChoices = [];
        foreach ($sliders as $slider) {
            $sliderChoices['#'.$slider->getId().' : '.$slider->getTitle()] = $slider->getId();
        }

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['slider', ChoiceType::class, [
                    'label' => 'Slider',
                    'translation_domain' => 'SonataBlockBundle',
                    'choices' => $sliderChoices,
                ]],
                ['template', TextType::class, [
                    'label' => 'Template',
                    'translation_domain' => 'SonataBlockBundle',
                ]],
                ['class', TextType::class, [
                    'label' => 'CSS Class',
                    'required' => false,
                    'translation_domain' => 'SonataBlockBundle',
                ]],
            ],
            'translation_domain' => 'SonataBlockBundle',
        ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'slider' => null,
            'class' => null,
            'template' => '@PartitechSonataExtra/Blocks/Slider/slider_default.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('Slider', null, null, 'SonataBlockBundle', [
            'class' => 'icon-block-slider',
        ]);
    }
}
