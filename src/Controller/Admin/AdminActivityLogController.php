<?php

namespace Partitech\SonataExtra\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Partitech\SonataExtra\Entity\AdminActivityEntityChangeLog;
use Partitech\SonataExtra\Entity\AdminActivityLog;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminActivityLogController extends CRUDController
{
    private TokenStorageInterface $tokenStorage;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    #[Required]
    public function autowireDependencies(
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): void {
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function purgeAction(): Response
    {
        $items = $this->entityManager->getRepository(AdminActivityLog::class)->findBy(['approval' => 0]);
        foreach ($items as $item) {
            $changeLogs = $item->getEntityChangeLogs();
            foreach ($changeLogs as $changeLog) {
                $this->entityManager->remove($changeLog);
            }
            $this->entityManager->remove($item);
        }

        $this->entityManager->flush();
        $message = $this->translator->trans('sonata-extra.approve.success_message.purge', [],'PartitechSonataExtraBundle');
        $this->addFlash('success', $message);

        return $this->redirectToRoute('admin_partitech_sonataextra_approval_list');
    }

    /**
     * @throws Exception
     */
    public function revertItemAction(?AdminActivityLog $id, ?AdminActivityEntityChangeLog $changeLog): Response
    {
        if (is_null($changeLog)) {
            $message = $this->translator->trans('sonata-extra.approve.error_message.journal_not_found', [],'PartitechSonataExtraBundle');
            $this->addFlash('error', $message);

            return $this->redirectToRoute('admin_partitech_sonataextra_adminactivitylog_list');
        }

        $entityClass = $changeLog->getEntityClass();
        $entityRepo = $this->entityManager->getRepository($entityClass);
        $entity = $entityRepo->find($changeLog->getEntityId());

        if (!$entity) {
            $message = $this->translator->trans('sonata-extra.approve.error_message.entity_not_found', [],'PartitechSonataExtraBundle');
            $this->addFlash('error', $message);

            return $this->redirectToRoute('admin_partitech_sonataextra_adminactivitylog_list');
        }

        $fieldName = $changeLog->getFieldName();
        $oldValue = $changeLog->getOldValue();
        $accessor = PropertyAccess::createPropertyAccessor();

        $oldValue = $this->convertIfNeeded($entity, $fieldName, $oldValue);

        $accessor->setValue($entity, $fieldName, $oldValue);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        $message = $this->translator->trans('sonata-extra.approve.success_message.revert_successfully', [],'PartitechSonataExtraBundle');
        $this->addFlash('success', $message);

        return $this->redirectToRoute('admin_partitech_sonataextra_adminactivitylog_list');
    }

    /**
     * @throws Exception
     */
    public function revertAction($id = null): Response
    {
        $adminActivityLogRepo = $this->entityManager->getRepository(AdminActivityLog::class);
        $adminActivityLog = $adminActivityLogRepo->find($id);

        if (!$adminActivityLog) {
            $message = $this->translator->trans('sonata-extra.approve.error_message.journal_not_found', [],'PartitechSonataExtraBundle');
            $this->addFlash('error', $message);

            return $this->redirectToRoute('admin_partitech_sonataextra_adminactivitylog_list');
        }

        $changeLogRepo = $this->entityManager->getRepository(AdminActivityEntityChangeLog::class);
        $changeLogs = $changeLogRepo->findBy(['adminActivityLog' => $adminActivityLog]);

        if (!$changeLogs) {
            $message = $this->translator->trans('sonata-extra.approve.error_message.journal_not_found_for_activity', [],'PartitechSonataExtraBundle');
            $this->addFlash('error', $message);

            return $this->redirectToRoute('admin_partitech_sonataextra_adminactivitylog_list');
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($changeLogs as $changeLog) {
            $entityClass = $changeLog->getEntityClass();
            $entityRepo = $this->entityManager->getRepository($entityClass);
            $entity = $entityRepo->find($changeLog->getEntityId());

            if ($entity) {
                $fieldName = $changeLog->getFieldName();
                $oldValue = $changeLog->getOldValue();
                $oldValue = $this->convertIfNeeded($entity, $fieldName, $oldValue);
                $accessor->setValue($entity, $fieldName, $oldValue);
                $this->entityManager->persist($entity);
            }
        }

        $this->entityManager->flush();
        $message = $this->translator->trans('sonata-extra.approve.success_message.all_revert_applied', [],'PartitechSonataExtraBundle');
        $this->addFlash('success', $message);

        return new RedirectResponse($this->admin->generateUrl('list', []));
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function approveAction($id = null): Response
    {
        $adminActivityLogRepo = $this->entityManager->getRepository(AdminActivityLog::class);
        $adminActivityLog = $adminActivityLogRepo->find($id);

        if (!$adminActivityLog) {
            $message = $this->translator->trans('sonata-extra.approve.error_message.journal_not_found', [],'PartitechSonataExtraBundle');
            $this->addFlash('error', $message);

            return $this->redirectToRoute('admin_partitech_sonataextra_approval_list');
        }
        $action = $adminActivityLog->getActionType();

        if ('create' == $action || 'update' == $action) {
            $changeLogRepo = $this->entityManager->getRepository(AdminActivityEntityChangeLog::class);
            $changeLogs = $changeLogRepo->findBy(['adminActivityLog' => $adminActivityLog]);

            if (!$changeLogs) {
                $message = $this->translator->trans('sonata-extra.approve.error_message.journal_not_found', [],'PartitechSonataExtraBundle');
                $this->addFlash('error', $message);

                return $this->redirectToRoute('admin_partitech_sonataextra_approval_list');
            }

            $accessor = PropertyAccess::createPropertyAccessor();
            $entityClass = $changeLogs[0]->getEntityClass();
            $entityRepo = $this->entityManager->getRepository($entityClass);

            if ('update' == $action) {
                $entity = $entityRepo->find($changeLogs[0]->getEntityId());
            } elseif ('create' == $action) {
                $entity = new $entityClass();
            }
            foreach ($changeLogs as $changeLog) {
                $fieldName = $changeLog->getFieldName();
                $NewValue = $changeLog->getNewValue();
                $accessorEntityClass = $this->getRefclass($entityClass, $fieldName);
                if ($accessorEntityClass) {
                    if ($NewValue) {
                        $accessorEntityClassRepo = $this->entityManager->getRepository($accessorEntityClass);
                        $accessorEntity = $accessorEntityClassRepo->find($NewValue);
                        $accessor->setValue($entity, $fieldName, $accessorEntity);
                    } else {
                        $metadata = $this->entityManager->getClassMetadata($entityClass);
                        $associations = $metadata->getAssociationMappings();

                        if (!empty($associations[$fieldName]) && \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE == $associations[$fieldName]['type']) {
                            // we are in a parent/child relationship. and we are the child.
                            if (null == $NewValue) {
                                $this->entityManager->remove($entity);
                            }
                        }
                    }
                } else {
                    $NewValue=$this->convertIfNeeded($entity, $fieldName, $NewValue);
                    $accessor->setValue($entity, $fieldName, $NewValue);
                }
                $entity->ignore_log = true;
            }
        } else {
            $data = $adminActivityLog->getData();
            $data_object = json_decode($data);
            $entityRepo = $this->entityManager->getRepository($adminActivityLog->getResource());
            $entity = $entityRepo->find($data_object->id);
            if (!empty($entity)) {
                $entity->ignore_log = true;
                $this->entityManager->remove($entity);
            }
        }
        if ('create' == $action) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
        $parentId = $entity;

        /* Gestion des relations OneToMany */
        $activityLog = $this->entityManager->getRepository(AdminActivityLog::class);
        $childObjects = $activityLog->findByTokenExceptId($adminActivityLog->getToken(), $adminActivityLog->getId());
        $refEntityClass = $entityClass;
        foreach ($childObjects as $object) {
            $adminActivityLogRepo = $this->entityManager->getRepository(AdminActivityLog::class);
            $adminActivityLog = $adminActivityLogRepo->find($object->getId());

            if ($adminActivityLog) {
                $action = $adminActivityLog->getActionType();
                if ('create' == $action || 'update' == $action) {
                    $changeLogRepo = $this->entityManager->getRepository(AdminActivityEntityChangeLog::class);
                    $changeLogs = $changeLogRepo->findBy(['adminActivityLog' => $adminActivityLog]);

                    $accessor = PropertyAccess::createPropertyAccessor();
                    $entityClass = $changeLogs[0]->getEntityClass();
                    $entityRepo = $this->entityManager->getRepository($entityClass);
                    $refField = $this->getRefField($entityClass, $refEntityClass);

                    if ('update' == $action) {
                        $entity = $entityRepo->find($changeLogs[0]->getEntityId());
                    } elseif ('create' == $action) {
                        $entity = new $entityClass();

                        if (!empty($refField)) {
                            $accessor->setValue($entity, $refField, $parentId);
                        }
                    }

                    foreach ($changeLogs as $i => $changeLog) {
                        $fieldName = $changeLog->getFieldName();
                        $NewValue = $changeLog->getNewValue();
                        $accessorEntityClass = $this->getRefclass($entityClass, $fieldName);

                        $metadata = $this->entityManager->getClassMetadata($entityClass);
                        $associations = $metadata->getAssociationMappings();

                        if (!empty($associations[$fieldName]) && \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE == $associations[$fieldName]['type']) {
                            // we are in a parent/child relationship. and we are the child.
                            if (null == $NewValue) {
                                if ('update' == $action) {
                                    $entity->ignore_log = true;
                                    $this->entityManager->remove($entity);
                                } else {
                                    $accessor->setValue($entity, $refField, $parentId);
                                    if (!empty($refField)) {
                                        $accessor->setValue($entity, $refField, $parentId);
                                    }
                                }
                            } else {
                                if ($NewValue) {
                                    $accessorEntityClassRepo = $this->entityManager->getRepository($accessorEntityClass);
                                    $accessorEntity = $accessorEntityClassRepo->find($NewValue);
                                    $accessor->setValue($entity, $fieldName, $accessorEntity);
                                }
                            }
                        } else {
                            if ($accessorEntityClass && $accessorEntityClass != $refEntityClass && $NewValue) {
                                $accessorEntityClassRepo = $this->entityManager->getRepository($accessorEntityClass);
                                $accessorEntity = $accessorEntityClassRepo->find($NewValue);
                                $accessor->setValue($entity, $fieldName, $accessorEntity);
                            } else {
                                $accessor->setValue($entity, $fieldName, $NewValue);
                            }
                        }
                        $entity->ignore_log = true;
                    }
                } else {
                    $data = $adminActivityLog->getData();
                    $data_object = json_decode($data);
                    $entityRepo = $this->entityManager->getRepository($adminActivityLog->getResource());
                    $entity = $entityRepo->find($data_object->id);
                    $entity->ignore_log = true;
                    $this->entityManager->remove($entity);
                }
                if ('create' == $action) {
                    $this->entityManager->persist($entity);
                    $entity->getId();
                }

                $this->entityManager->flush();
            }
        }

        if ('update' == $action) {
            $message = $this->translator->trans('sonata-extra.approve.success_message.all_applied', [],'PartitechSonataExtraBundle');
            $this->addFlash('success', $message);
        }

        if ('create' == $action) {
            $message = $this->translator->trans('sonata-extra.approve.success_message.create_applied', [],'PartitechSonataExtraBundle');
            $this->addFlash('success', $message);
        }

        $user = null;
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser()) {
            $user = $token->getUser();
        }

        $current_date = (new \DateTime())->format('Y-m-d H:i:s');
        $description = $adminActivityLog->getDescription();

        $message = $this->translator->trans('sonata-extra.approve.approved_by_message', [],'PartitechSonataExtraBundle');
        $description .= str_replace('[user]', $user->getUsername(), $message);
        $description = str_replace('[date]', $current_date, $description);

        $conn = $this->entityManager->getConnection();

        $stmt = $conn->prepare('update sonata_extra__admin_activity_log set approval=1, description=:description where id=:id');
        $stmt->executeQuery([
            'id' => $id,
            'description' => $description,
        ]);

        return $this->redirectToRoute('admin_partitech_sonataextra_approval_list');
    }

    private function getRefclass($entityClass, $field): mixed
    {
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        foreach ($metadata->associationMappings as $object) {
            if ($object['fieldName'] == $field) {
                return $object['targetEntity'];
            }
        }
        return null;
    }

    private function getRefField($entityClass, $refEntityClass): mixed
    {
        $metadata = $this->entityManager->getClassMetadata($entityClass);
        foreach ($metadata->associationMappings as $object) {
            if ($object['targetEntity'] == $refEntityClass) {
                return $object['fieldName'];
            }
        }
        return null;
    }

    /**
     * @throws Exception
     */
    private function convertIfNeeded(object $entity, string $fieldName, mixed $value): mixed
    {
        $metadata = $this->entityManager->getClassMetadata(get_class($entity));
        $fieldType = $metadata->fieldMappings[$fieldName]['type'];
        if ('datetime' === $fieldType && is_string($value)) {
            $value = new \DateTime($value);
        }

        return $value;
    }
}
