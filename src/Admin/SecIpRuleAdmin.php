<?php

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Partitech\SonataExtra\Entity\SecIpRule;
use Psr\Cache\InvalidArgumentException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'Security Ip Rule',
    model_class: SecIpRule::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
class SecIpRuleAdmin extends AbstractAdmin
{
    private CacheInterface $cache;

    #[Required]
    public function required(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('ip', TextType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('ip');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('ip');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('ip');
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
        $this->cache->delete('ip_rules_cache');
    }
}
