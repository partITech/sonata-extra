<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Partitech\SonataExtra\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Partitech\SonataExtra\Entity\SecStopWord;
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
    label: 'Security Stop Word',
    model_class: SecStopWord::class,
    calls: [
        ['setTranslationDomain', ['PartitechSonataExtraBundle']],
    ]
)]
class SecStopWordAdmin extends AbstractAdmin
{
    private CacheInterface $cache;

    #[Required]
    public function required(
        CacheInterface $cache
    ): void {
        $this->cache = $cache;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form->add('word', TextType::class);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('word');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('word');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show->add('word');
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
        $this->cache->delete('stop_words_cache');
    }
}
