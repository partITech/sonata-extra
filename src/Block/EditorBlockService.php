<?php
namespace Partitech\SonataExtra\Block;

use  Partitech\SonataExtra\Entity\Editor;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Partitech\SonataExtra\Entity\Article;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

#[AutoconfigureTag(name: 'sonata.block')]
final class EditorBlockService extends AbstractBlockService implements EditableBlockService
{
    private $entityManager;
    private ParameterBagInterface $parameterBag;
    private CmsManagerSelectorInterface $cmsSelector;
    private $categoryManager;
    private PaginatorInterface $paginator;

    #[Required]
    public function autowireDependencies(
        Environment $twig,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag,
        CmsManagerSelectorInterface $cmsSelector,
        CategoryManagerInterface $categoryManager,
        PaginatorInterface $paginator
    ): void {
        parent::__construct($twig);
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
        $this->cmsSelector = $cmsSelector;
        $this->categoryManager = $categoryManager;
        $this->paginator = $paginator;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        $page = $blockContext->getBlock()->getSetting('page', 1);



        $cms = $this->cmsSelector->retrieve();
        $site = $cms->getCurrentPage()->getSite();

        $settings = $blockContext->getSettings();

        $class = $settings['class'];
        $template = $settings['template'];
        $editor = $settings['editor'];

        $editor=$this->entityManager->getRepository(Editor::class)->findOneBy(['id'=>$editor]);

        if($editor->getSite()!=$site){
            if(!empty($editor->translations[$site->getId()]['entity_id'])){
                $editor=$this->entityManager->getRepository(Editor::class)->findOneBy(['id'=>$editor->translations[$site->getId()]['entity_id']]);
            }
        }

        return $this->renderResponse($template, [
            'block' => $blockContext->getBlock(),
            'content' => $editor->getHtmlContent(),
            'class' => $class,
        ], $response);
    }





    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $formMapper, BlockInterface $block): void
    {

        $editor = $this->entityManager->getRepository(Editor::class)->findAll();

        $editorChoices = [];
        foreach ($editor as $category) {
            $editorChoices[$category->getTitle()] = $category->getId();
        }

        $formMapper->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['editor', ChoiceType::class, [
                    'label' => 'Content editor',
                    'translation_domain' => 'SonataBlockBundle',
                    'choices' => $editorChoices,
                ]],
                ['template', TextType::class, [
                    'label' => 'Template',
                    'translation_domain' => 'SonataExtraBundle',
                ]],
                ['class', TextType::class, [
                    'label' => 'CSS Class',
                    'required' => false,
                    'translation_domain' => 'SonataExtraBundle',
                ]],
            ],
            'translation_domain' => 'SonataExtraBundle',
        ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'editor' => null,
            'class' => null,
            'template' => '@PartitechSonataExtra/Blocks/editor/content.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('Content : Editor ', null, null, 'SonataBlockBundle', [
            'class' => 'fa fa-book-open',
        ]);
    }
}
