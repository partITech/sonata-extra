<?php
namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Partitech\SonataExtra\Entity\SecStopWord;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Service\Attribute\Required;
#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'Security Stop Word',
    model_class: SecStopWord::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
class SecStopWordAdmin extends AbstractAdmin
{

    #[Required]
    public function required(
        CacheInterface $cache
    ): void {
        $this->cache=$cache;
    }

    protected function configureFormFields(FormMapper $formMapper):void
    {
        $formMapper->add('word', TextType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper):void
    {
        $datagridMapper->add('word');
    }

    protected function configureListFields(ListMapper $listMapper):void
    {
        $listMapper->addIdentifier('word');
    }

    protected function configureShowFields(ShowMapper $showMapper):void
    {
        $showMapper->add('word');
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
        $this->cache->delete('stop_words_cache');
    }
}
