<?php

namespace Partitech\SonataExtra\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GutenbergType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // ...
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['gutenberg_assets'] = [
            'css' => [
                '/bundles/partitechsonataextra/gutenberg/isolated-block-editor-trunk/build-browser/core.css',
                '/bundles/partitechsonataextra/gutenberg/isolated-block-editor-trunk/build-browser/isolated-block-editor.css',
                '/themes/partitech/css/main.css',
            ],
            'js' => [
                '/bundles/partitechsonataextra/gutenberg/isolated-block-editor-trunk/build-browser/isolated-block-editor.js',
            ],
        ];
        $view->vars['context'] = $options['context'];

        $categories = [];
        foreach ($options['patterns'] as $p) {
            $categories = array_merge($categories, $p['categories']);
        }
        foreach ($categories as $c) {
            $options['categories'][] = ['label' => $c, 'name' => $c];
        }

        if (!empty($options['media_patterns'])) {
            $options['categories'][] = ['label' => $options['media_patterns'][0], 'name' => $options['media_patterns'][0]];
            foreach ($options['media_patterns'][1] as $media) {
                $media['categories'][] = $options['media_patterns'][0];
                $options['patterns'][] = $media;
            }
        }
        $view->vars['patterns'] = json_encode($options['patterns'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

        $view->vars['categories'] = json_encode($this->array_unique_multidimensional($options['categories']));

        $additionalBlocks = [
            'core/pattern',
        ];
        $mergedBlocks = array_merge($options['allowed_blocks'], $additionalBlocks);

        $view->vars['allowed_blocks'] = json_encode($mergedBlocks);
    }

    public function getParent()
    {
        return TextareaType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'context' => 'default',
            'patterns' => [],
            'media_patterns' => [],
            'categories' => [],
            'allowed_blocks' => [
                    'core/pattern',
                    'core/block',
                    'core/group',

                    'core/code',
                    'core/html',
                    'core/pullquote',
                    'core/table',
                    'core/text-columns',

                    'core/preformatted',
                    'core/paragraph',
                    'core/image',
                    'core/heading',
                    'core/gallery',
                    'core/list',
                    'core/list-item',
                    'core/quote',
                    'core/archives',
                    'core/audio',
                    'core/button',
                    'core/buttons',
                    'core/calendar',
                    'core/categories',
                    'core/code',
                    'core/column',
                    'core/columns',
                    'core/cover',
                    'core/details',
                    'core/embed',
                    'core/file',
                    'core/group',
                    'core/html',
                    'core/latest-comments',
                    'core/latest-posts',
                    'core/media-text',
                    'core/missing',
                    'core/more',
                    'core/nextpage',
                    'core/page-list',
                    'core/page-list-item',

                    'core/preformatted',
                    'core/pullquote',

                    'core/rss',
                    'core/search',
                    'core/separator',
                    'core/shortcode',
                    'core/social-link',
                    'core/social-links',
                    'core/spacer',
                    'core/table',
                    'core/tag-cloud',
                    'core/text-columns',
                    'core/verse',
                    'core/video',
                    'core/footnotes',
                    'core/navigation',
                    'core/navigation-link',
                    'core/navigation-submenu',
                    'core/site-logo',
                    'core/site-title',
                    'core/site-tagline',
                    'core/query',
                    'core/template-part',
                    'core/avatar',
                    'core/post-title',
                    'core/post-excerpt',
                    'core/post-featured-image',
                    'core/post-content',
                    'core/post-author',
                    'core/post-author-name',
                    'core/post-date',
                    'core/post-terms',
                    'core/post-navigation-link',
                    'core/post-template',
                    'core/query-pagination',
                    'core/query-pagination-next',
                    'core/query-pagination-numbers',
                    'core/query-pagination-previous',
                    'core/query-no-results',
                    'core/read-more',
                    'core/comments',
                    'core/comment-author-name',
                    'core/comment-content',
                    'core/comment-date',
                    'core/comment-edit-link',
                    'core/comment-reply-link',
                    'core/comment-template',
                    'core/comments-title',
                    'core/comments-pagination',
                    'core/comments-pagination-next',
                    'core/comments-pagination-numbers',
                    'core/comments-pagination-previous',
                    'core/post-comments-form',
                    'core/home-link',
                    'core/loginout',
                    'core/query-title',
                    'core/post-author-biography',
                    'core/columns',
            ],
        ]);
    }

    public function array_unique_multidimensional($array)
    {
        $serialized = array_map('serialize', $array);
        $unique = array_unique($serialized);
        $unique = array_intersect_key($array, $unique);

        // Réindexer les clés du tableau
        return array_values($unique);
    }
}
