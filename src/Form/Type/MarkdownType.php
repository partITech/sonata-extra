<?php

namespace Partitech\SonataExtra\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarkdownType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);
        $view->vars['markdown_assets'] = [
            'css' => [
                'https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css',
                'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/default.min.css'
            ],
            'js' => [
                'https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js',
                'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js'
            ],
        ];


        $view->vars['template'] = '@PartitechSonataExtra/Form/markdown_field.html.twig';
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        ]);
    }


}
