<?php

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;

use Partitech\SonataExtra\Entity\SecFirewallRule;
use Psr\Cache\InvalidArgumentException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'Security Firewall Rule',
    model_class: SecFirewallRule::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
class SecFirewallRuleAdmin extends AbstractAdmin
{
    private CacheInterface $cache;

    #[Required]
    public function required(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('label')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Stop Word' => 'stop_word',
                    'IP' => 'ip',
                    'User Agent' => 'user_agent',
                    'Stop Word DB' => 'stop_word_db',
                    'IP DB' => 'ip_db',
                ],
            ])
            ->add('source', ChoiceType::class, [
                'choices' => [
                    'GET' => 'get',
                    'POST' => 'post',
                    'Header' => 'header',
                ],
            ])
            ->add('matchMode', ChoiceType::class, [
                'choices' => [
                    'Equal' => 'equal',
                    'Contain' => 'contain',
                ],
            ])
            ->add('parameters', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'required' => false,
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('type');
        $filter->add('source');
        $filter->add('parameters');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('label', null, [
            'route' => [
                'name' => 'edit'
            ]
        ]);
        $list->add('type', 'choice', [
            'editable' => true,
            'choices' => [
                'Stop Word' => 'stop_word',
                'IP' => 'ip',
                'User Agent' => 'user_agent',
                'Stop Word DB' => 'stop_word_db',
                'IP DB' => 'ip_db',
            ]
        ]);
        $list->add('source', 'choice', [
            'editable' => true,
            'choices' => [
                'GET' => 'get',
                'POST' => 'post',
                'Header' => 'header',
            ]
        ]);
        $list->add('matchMode', 'choice', [
            'editable' => true,
            'choices' => [
                'Equal' => 'equal',
                'Contain' => 'contain',
            ]
        ]);
        $list->add('parameters', null, [
            'template' => '@PartitechSonataExtra/Admin/secFirewall/list_field_parameters.html.twig'
        ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {

        $show->add('type');
        $show->add('source');
        $show->add('matchMode');
        $show->add('parameters');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function postUpdate($object): void
    {
        $this->clearCache();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function postPersist($object): void
    {
        $this->clearCache();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function postRemove($object): void
    {
        $this->clearCache();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function clearCache(): void
    {
        $this->cache->delete('firewall_rules_cache');
    }
}
