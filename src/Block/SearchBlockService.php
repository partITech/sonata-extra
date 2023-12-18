<?php

namespace Partitech\SonataExtra\Block;

use Partitech\SonataExtra\Entity\Contact;
use Partitech\SonataExtra\Form\ContactType;
use Partitech\SonataExtra\Form\SearchType;
use Partitech\SonataExtra\Repository\ContactRepository;
use Partitech\SonataExtra\Service\ContactBlockMailerService;
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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

#[AutoconfigureTag(name: 'sonata.block')]
final class SearchBlockService extends AbstractBlockService implements EditableBlockService
{
    public function __construct(
        Environment $twig,
        private readonly TranslatorInterface $translator,
        private readonly RequestStack $requestStack,
        private readonly FormFactoryInterface $formFactory
    ) {
        parent::__construct($twig);
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response|RedirectResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $settings = $blockContext->getSettings();

        $searchValue = $request->query->get('s');
        $placeholder = $settings['placeholder'] ?? null;
        $submitLabel = $settings['submitLabel'] ?? null;
        
        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'placeholder'=> $placeholder,
            'settings' => $blockContext->getSettings(),
            'search_value' => $searchValue,
            'submit_label' => $submitLabel
        ], $response);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        
        $form->add('settings', ImmutableArrayType::class, [
        'keys' => [
            [
                'placeholder',
                TextType::class,
                [
                    'label' => 'sonata-extra.search.placeholder',
                    'translation_domain' => 'PartitechSonataExtraBundle',
                    'required' => false,
                ],
            ],
            [
                'submitLabel',
                TextType::class,
                [
                    'label' => 'sonata-extra.search.submit_label',
                    'translation_domain' => 'PartitechSonataExtraBundle',
                    'required' => false,
                ],
            ],

            [
                'template',
                TextType::class,
                [
                    'label' => 'Template',
                    'translation_domain' => 'PartitechSonataExtraBundle',
                ]
            ],
        ],
        'translation_domain' => 'PartitechSonataExtraBundle',
    ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {}

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'placeholder' => "Search",
            'submitLabel' => $this->translator->trans(
                'sonata-extra.search.submit_label', [],
                'PartitechSonataExtraBundle'
            ),
            'template' => '@PartitechSonataExtra/Blocks/search/form.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('Search', null, null, 'SonataBlockBundle', [
            'class' => 'fa fa-search',
        ]);
    }
}
