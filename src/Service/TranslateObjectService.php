<?php

namespace Partitech\SonataExtra\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Partitech\SonataExtra\Attribute\Translatable;
use Partitech\SonataExtra\Traits\ControllerTranslationTrait;
use Partitech\SonataExtra\SmartService\SmartServiceProviderFactoryInterface;
use ReflectionClass;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\PageBundle\Entity\SiteManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Partitech\SonataExtra\SmartService\TranslationCreateTemplateService;

use Doctrine\ORM\Mapping\ClassMetadata;

class TranslateObjectService
{
    use ControllerTranslationTrait;

    private TokenStorageInterface $tokenStorage;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;
    private Pool $adminPool;
    private ParameterBagInterface $parameterBag;
    private SmartServiceProviderFactoryInterface $smartServiceProviderFactory;
    private TranslationCreateTemplateService $TranslationCreateTemplateService;
    private SiteManager $site;
    private mixed $smart_service_conf;
    private bool $createTranslationTemplate;
    private string $smartServiceTranslation;

    #[Required]
    public function autowireDependencies(
        TokenStorageInterface                $tokenStorage,
        EntityManagerInterface               $entityManager,
        TranslatorInterface                  $translator,
        Pool                                 $adminPool,
        ParameterBagInterface                $parameterBag,
        SmartServiceProviderFactoryInterface $smartServiceProviderFactory,
        TranslationCreateTemplateService     $TranslationCreateTemplateService,
    ): void
    {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->adminPool = $adminPool;
        $this->parameterBag = $parameterBag;
        $this->smartServiceProviderFactory = $smartServiceProviderFactory;
        $this->TranslationCreateTemplateService = $TranslationCreateTemplateService;
        if (!empty($_SERVER['argv']) && !empty($_SERVER['argv'][1])) {
            $this->createTranslationTemplate = $_SERVER['argv'][1] == 'sonata:extra:translation-create-template' ?? true;
        } else {
            $this->createTranslationTemplate = false;
        }
        $this->smartServiceTranslation = $this->parameterBag->get('partitech_sonata_extra.smart_service.provider.translation');
    }

    public function createTranslation($id, $from_site, $to_site, $fqcn)
    {

        $this->smart_service_conf = $this->parameterBag->get('partitech_sonata_extra.smart_service');


        $admin = $this->adminPool->getAdminByAdminCode($fqcn);
        $object = $admin->getObject($id);
        $translations = $object->getTranslations();

        if (!empty($translations[$from_site]) && !empty($translations[$from_site]['entity_id'])) {
            $object = $admin->getObject($translations[$from_site]['entity_id']);
        }


        $hasClone = method_exists($object, '__clone');

        // get cibling site
        $siteClass = $this->parameterBag->get('sonata.page.site.class');
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('s')
            ->from($siteClass, 's')
            ->where('s.id = :id')
            ->setParameter('id', $to_site);
        $site = $qb->getQuery()->getOneOrNullResult();
        $this->site = $site;

        if (!$object) {
            throw $this->createNotFoundException(sprintf('Unable to find the object with id: %s', $id));
        }

        if ($hasClone) {
            $clonedObject = clone $object;
            $clonedObject = $this->translateEntity($clonedObject);

            // check one2Many relations.
            $metadata = $this->entityManager->getClassMetadata(get_class($object));
            $associations = $metadata->getAssociationMappings();

            foreach ($associations as $relations) {
                $field_name = $relations['fieldName'];
                if ('site' != $field_name && 'context' != $field_name && ClassMetadata::ONE_TO_MANY == $relations['type']) {
                    $relation_items = $this->entityPropertyGet($clonedObject, $field_name);

                    $newRelatedEntities = [];
                    foreach ($relation_items as $item) {
                        $item = clone $item;
                        $newRelatedEntities[] = $this->recursive_translate($item);
                    }
                    $clonedCollection = new ArrayCollection($newRelatedEntities);
                    $clonedObject = $this->entityPropertySet($clonedObject, $field_name, $clonedCollection);
                }
            }
        } else {
            $clonedObject = $this->cloneObject($object);
            $metadata = $this->entityManager->getClassMetadata(get_class($clonedObject));
            $associations = $metadata->getAssociationMappings();

            foreach ($associations as $relations) {
                $field_name = $relations['fieldName'];
                if ('site' != $field_name && 'context' != $field_name && ClassMetadata::ONE_TO_MANY == $relations['type']) {
                    $relation_items = $this->entityPropertyGet($clonedObject, $field_name);
                    $newRelatedEntities = [];

                    foreach ($relation_items as $i => $item) {
                        $cloneAndTranslated = $this->cloneObject($item, $relations['mappedBy'], $clonedObject);
                        $newRelatedEntities[] = $cloneAndTranslated;
                    }
                    $clonedCollection = new ArrayCollection($newRelatedEntities);
                    $clonedObject = $this->entityPropertySet($clonedObject, $field_name, $clonedCollection);
                } elseif ('site' != $field_name && 'context' != $field_name && ClassMetadata::MANY_TO_ONE == $relations['type']) {
                    $clonedObject = $this->entityPropertySet($clonedObject, $field_name, null);
                }
            }
        }

        $clonedObject->setId(null);
        $clonedObject->setSite($this->site);
        $clonedObject->setTranslationFromId($object->getTranslationFromId());
        if (method_exists($clonedObject, 'setParent')) {
            // if method exist set parent to null
            $clonedObject->setParent(null);
        }
        //If slug exist, and the initial slug and the new object slug is equal, set it unique by concatenating its locale value
        if (method_exists($clonedObject, 'setSlug') && $clonedObject->getSlug() == $object->getSlug()) {
            $slugger = new AsciiSlugger();
            $slug = $slugger->slug($this->site->getLocale() . '-' . $clonedObject->getSlug())->lower();
            $clonedObject->setSlug($slug);
        }


        //check if the object allready exist
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('p')
            ->from($admin->getClass(), 'p')
            ->where('p.site = :site')
            ->andWhere('p.translation_from_id = :translation_from_id')
            ->setParameter('site', $this->site)
            ->setParameter('translation_from_id', $object->getTranslationFromId());
        $item = $qb->getQuery()->getOneOrNullResult();


        if (!$item) {
            $this->entityManager->persist($clonedObject);
            $this->entityManager->flush();
            return $clonedObject;
        } else {
            return $item;
        }


    }

    public function recursive_translate($object)
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($object));
        $associations = $metadata->getAssociationMappings();

        foreach ($associations as $relations) {
            $field_name = $relations['fieldName'];
            if ('site' != $field_name && 'context' != $field_name && ClassMetadata::ONE_TO_MANY == $relations['type']) {
                $relation_items = $this->entityPropertyGet($object, $field_name);

                $newRelatedEntities = [];
                if (!empty($relation_items)) {
                    foreach ($relation_items as $item) {
                        $item = $this->translateEntity($item);
                        $newRelatedEntities[] = $this->recursive_translate($item);
                    }
                }

                $clonedCollection = new ArrayCollection($newRelatedEntities);
                $object = $this->entityPropertySet($object, $field_name, $clonedCollection);
            }
        }
        return $this->translateEntity($object);
    }

    public function entityPropertySet($entity, $propertyName, $value)
    {
        if (is_object($entity)) {
            $reflectionClass = new ReflectionClass(get_class($entity));
            $property = $reflectionClass->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue($entity, $value);
        }

        return $entity;
    }

    public function entityPropertyGet($entity, $propertyName)
    {
        $reflectionClass = new ReflectionClass(get_class($entity));
        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);
        $value = $property->getValue($entity);

        return $value;
    }

    public function entityPropertyAdd($entity, $propertyName, $inversedPropertyName, $objectToAdd)
    {
        // cancel inversed value of the clone object we want to add to our collection
        $reflectionClass = new ReflectionClass(get_class($objectToAdd));
        $property = $reflectionClass->getProperty($inversedPropertyName);
        $property->setAccessible(true);
        $property->setValue($objectToAdd, null);

        // translate it before add it to our object
        $objectToAdd = $this->translateEntity($objectToAdd);

        // add the clean cloned objet to our collection
        $reflectionClass = new ReflectionClass($entity);
        $property = $reflectionClass->getProperty($propertyName);
        $property->setAccessible(true);
        $collection = $property->getValue($entity);

        // clean item collection if they have an id.
        foreach ($collection as $c) {
            if ($this->entityPropertyGet($c, 'id')) {
                $collection->removeElement($c);
            }
        }

        $metadata = $this->entityManager->getClassMetadata(get_class($objectToAdd));
        $associations = $metadata->getAssociationMappings();
        foreach ($associations as $relation) {
            $field = $relation['fieldName'];
            if (ClassMetadata::ONE_TO_MANY == $relation['type']) {
                $relatedEntities = $this->entityPropertyGet($objectToAdd, $field);
                foreach ($relatedEntities as $relatedEntity) {
                    $objectToAdd = $this->entityPropertyAdd($objectToAdd, $field, $relation['mappedBy'], $relatedEntity);  // Appel récursif
                }
            }
        }

        $collection->add($objectToAdd);
        $property->setValue($entity, $collection);

        return $entity;
    }

    private function translateEntity($object)
    {
        if (true == $this->smart_service_conf['translate_on_create_translation'] || $this->createTranslationTemplate) {
            $reflectionClass = new ReflectionClass($object);
            $translate_array = [];

            foreach ($reflectionClass->getProperties() as $property) {
                $attributes = $property->getAttributes(Translatable::class);

                if (empty($attributes)) {
                    $attributes = $property->getAttributes(\Partitech\SonataMenu\Model\Translatable::class);
                }
                $propertyName = $property->getName();

                if ($attributes) {
                    $value = $this->EntityPropertyGet($object, $propertyName);
                    if (!empty($value) && !is_object($value)) {
                        $translate_array[$propertyName] = $this->EntityPropertyGet($object, $propertyName);
                    }
                }
            }


            if ($translate_array) {
                $translationProvider = $this->smartServiceProviderFactory->create($this->smartServiceTranslation);
                if ($this->createTranslationTemplate) {
                    $className = $this->TranslationCreateTemplateService->getClassNameByObject($object);
                    $translationProvider->createTemplateFromArray($className, $object->getId(), $translate_array, $this->site);
                } else {
                    $translation = $translationProvider->translateArray($translate_array, $this->site->getLocale());
                    // Set the translated string to the cloned object properties.
                    if ($translation) {
                        foreach ($translation as $propertyName => $newValue) {
                            $object = $this->entityPropertySet($object, $propertyName, $newValue['translated']);
                        }
                    }
                }

            }
        }

        return $object;
    }

    /**
     * @throws \ReflectionException
     */
    private function cloneObject($object, $mappedBy = false, $refclonedObject = false)
    {
        $reflectionClass = new ReflectionClass($object);
        if ($reflectionClass->hasMethod('__clone')) {
            return $this->translateEntity($object);
        } else {
            $clonedObject = clone $object;
            $metadata = $this->entityManager->getClassMetadata(get_class($clonedObject));
            $associations = $metadata->getAssociationMappings();
            foreach ($associations as $relations) {
                $field_name = $relations['fieldName'];
                if ('site' != $field_name && 'context' != $field_name && ClassMetadata::MANY_TO_ONE == $relations['type']) {
                    $clonedObject = $this->entityPropertySet($clonedObject, $field_name, null);
                }
            }

            try {
                $property = $reflectionClass->getProperty($mappedBy);
                $property->setAccessible(true);
                $property->setValue($clonedObject, $refclonedObject);

                $property = $reflectionClass->getProperty('id');
                $property->setAccessible(true);
                $property->setValue($clonedObject, null);
                return $this->translateEntity($clonedObject);
            } catch (\ReflectionException $e) {
                return $this->translateEntity($clonedObject);
            }
        }
    }

}