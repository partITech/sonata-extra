<?php
namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;

use Partitech\SonataExtra\Entity\SecFirewallRule;
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
    #[Required]
    public function required(
        CacheInterface $cache
    ): void {
        $this->cache=$cache;
    }

    protected function configureFormFields(FormMapper $formMapper):void
    {
        $formMapper
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper):void
    {
        $datagridMapper->add('type');
        $datagridMapper->add('source');
        $datagridMapper->add('parameters');
    }

    protected function configureListFields(ListMapper $listMapper):void
    {
        $listMapper->addIdentifier('label', null, [
            'route' => [
                'name' => 'edit'
            ]
        ]);
        $listMapper->add('type', 'choice', [
            'editable' => true,
            'choices' => [
                'Stop Word' => 'stop_word',
                'IP' => 'ip',
                'User Agent' => 'user_agent',
                'Stop Word DB' => 'stop_word_db',
                'IP DB' => 'ip_db',
            ]
        ]);
        $listMapper->add('source',  'choice', [
            'editable' => true,
            'choices' => [
                'GET' => 'get',
                'POST' => 'post',
                'Header' => 'header',
                'Header' => 'header',
            ]
        ]);
        $listMapper->add('matchMode', 'choice', [
            'editable' => true,
            'choices' => [
                'Equal' => 'equal',
                'Contain' => 'contain',
            ]
        ]);
        $listMapper->add('parameters', null, [
            'template' => '@PartitechSonataExtra/Admin/secFirewall/list_field_parameters.html.twig'
        ]);
    }

    protected function configureShowFields(ShowMapper $showMapper):void
    {

        $showMapper->add('type');
        $showMapper->add('source');
        $showMapper->add('matchMode');
        $showMapper->add('parameters');
    }

    public function postUpdate($object):void
    {
        $this->clearCache();
    }

    public function postPersist($object):void
    {
        $this->clearCache();
    }

    public function postRemove($object):void
    {
        $this->clearCache();
    }

    private function clearCache()
    {
        $this->cache->delete('firewall_rules_cache');
    }
}
