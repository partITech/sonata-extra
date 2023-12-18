<?php

namespace Partitech\SonataExtra\EventListener;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\Event\onFlushEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ObjectManager;
use JMS\Serializer\SerializerInterface;
use Partitech\SonataExtra\Entity\AdminActivityLog;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Event\BatchActionEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
//use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

class DoctrineActivityListener
{
    private TokenStorageInterface $tokenStorage;
    private SerializerInterface $serializer;
    private RequestStack $requestStack;
    private RouterInterface $router;
    //private CsrfTokenManagerInterface $csrfTokenManager;
    private LoggerInterface $logger;
    private Security $security;
    private ParameterBagInterface $parameterBag;
    private TranslatorInterface $translator;

    #[Required]
    public function autowireDependencies(
        ParameterBagInterface $parameterBag,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        RouterInterface $router,
        //CsrfTokenManagerInterface $csrfTokenManager,
        Security $security,
        TranslatorInterface $translator
    ): void {
        $this->parameterBag = $parameterBag;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
        $this->requestStack = $requestStack;
        $this->router = $router;
        //$this->csrfTokenManager = $csrfTokenManager;
        $this->logger = $logger;
        $this->security = $security;
        $this->translator = $translator;
    }

    public function onPreBatchAction(BatchActionEvent $event)
    {

        if (!$this->needAproval()) {
            return true;
        }

        $request = $this->requestStack->getCurrentRequest();
        $currentRoute = $request->attributes->get('_route');

        $modelClass = $event->getAdmin()->getModelClass();

        $entity = new $modelClass();
        if (!$this->excluded_entity($entity)) {
            $objectManager = $event->getProxyQuery()->getQueryBuilder()->getEntityManager();
            foreach ($event->getIdx() as $idx) {
                $adminActivityLogRepo = $objectManager->getRepository($modelClass);
                $entity = $adminActivityLogRepo->find($idx);
                $adminLogId=$this->logActivity('delete', $entity, $objectManager);
            }

            $event->stopPropagation();
            $listRoute = str_replace('_batch', '_list', $currentRoute);
            $url = $this->router->generate($listRoute);
            $response = new RedirectResponse($url);
            $response->send();
            exit;
        }
        return true;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        if ($this->needAproval()) {
            return;
        }
        $entity = $args->getObject();
        if ($this->excluded_entity($entity)) {
            return;
        }
        if ($entity instanceof AdminActivityLog) {
            return;  // Skip logging for ActivityLogAdmin entities
        }
        if ($this->isAdmin()) {
            $objectManager = $args->getObjectManager();
            $adminLogId=$this->logActivity('create', $entity, $objectManager);

        }
    }

    /**
     * @ORM\PreUpdate
     *
     * @throws Exception
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {

        if ($this->needAproval()) {
            return;
        }

        $entity = $args->getObject();
        if ($this->excluded_entity($entity)) {
            return;
        }
        if ($entity instanceof AdminActivityLog) {
            return;  // Skip logging for ActivityLogAdmin entities
        }
        $objectManager = $args->getObjectManager();

        if ($this->isAdmin()) {
            $adminLogId=$this->logActivity('update', $entity, $objectManager);
            $changeSet = $args->getEntityChangeSet($entity);
            $this->logChangeSet('update', $objectManager, $changeSet, $entity, $adminLogId);

        }
        /*if ($this->isAdmin()) {
            $adminLogId = $this->logActivity('update', $entity, $objectManager);

            $conn = $objectManager->getConnection();
            $changeSet = $args->getEntityChangeSet();

            foreach ($changeSet as $fieldName => [$oldValue, $newValue]) {

                if($oldValue instanceof \DateTime){
                    $oldValue = $oldValue->format('Y-m-d H:i:s');
                }

                if($newValue instanceof \DateTime){
                    $newValue = $newValue->format('Y-m-d H:i:s');
                }


                $sql = '
                INSERT INTO admin_activity_entity_change_log
                (admin_activity_log_id, entity_class, entity_id, field_name, old_value, new_value)
                VALUES
                (:adminLogId, :entityClass, :entityId, :fieldName, :oldValue, :newValue)
            ';

                $params = [
                    'adminLogId' => $adminLogId,
                    'entityClass' => get_class($entity),
                    'entityId' => $entity->getId(),
                    'fieldName' => $fieldName,
                    'oldValue' => $oldValue,
                    'newValue' => $newValue,
                ];

                $conn->executeQuery($sql, $params);
            }
        }*/
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemove(PreRemoveEventArgs $args): void
    {
        if ($this->needAproval()) {
            return;
        }

        $entity = $args->getObject();
        if ($this->excluded_entity($entity)) {
            return;
        }
        if ($entity instanceof AdminActivityLog) {
            return;  // Skip logging for ActivityLogAdmin entities
        }
        $objectManager = $args->getObjectManager();
        if ($this->isAdmin()) {
            $this->logActivity('delete', $entity, $objectManager);
        }
    }

    /**
     * @ORM\onFlush
     */
    public function onFlush(onFlushEventArgs $args): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$this->needAproval() || null === $request) {
            return;
        }

        $currentRoute = $request->attributes->get('_route');

        $objectManager = $args->getObjectManager();
        $uow = $objectManager->getUnitOfWork();

        // Clear scheduled entity insertions
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->logger->error('scheduled entity insertions');
            if (!$this->excluded_entity($entity)) {
                if (!isset($entity->ignore_log) || !$entity->ignore_log) {
                    $adminLogId = $this->logActivity('create', $entity, $objectManager);
                    $changeSet = $uow->getEntityChangeSet($entity);
                    $this->logChangeSet('create', $objectManager, $changeSet, $entity, $adminLogId);
                    $uow->detach($entity);

                    $listRoute = str_replace('_create', '_list', $currentRoute);
                    $listRoute = str_replace('_edit', '_list', $listRoute);
                    $url = $this->router->generate($listRoute);
                    $response = new RedirectResponse($url);
                    $response->send();

                    // $args->stopPropagation();
                }
            }
        }

        // Clear scheduled entity updates
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->logger->error('scheduled entity updates');

            if (!$this->excluded_entity($entity)) {
                if (!isset($entity->ignore_log) || !$entity->ignore_log) {
                    $adminLogId = $this->logActivity('update', $entity, $objectManager);
                    $changeSet = $uow->getEntityChangeSet($entity);
                    $this->logChangeSet('update', $objectManager, $changeSet, $entity, $adminLogId);
                    $uow->detach($entity);
                }
            }
        }

        // Clear scheduled entity deletions
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $this->logger->error('scheduled entity deletions');
            if (!$this->excluded_entity($entity)) {
                if (!isset($entity->ignore_log) || !$entity->ignore_log) {
                    $adminLogId = $this->logActivity('delete', $entity, $objectManager);
                    $changeSet = $uow->getEntityChangeSet($entity);
                    $this->logChangeSet('delete', $objectManager, $changeSet, $entity, $adminLogId);
                    $uow->detach($entity);
                    if (0 !== substr_compare($currentRoute, '_batch', -strlen('_batch'))) {
                        $listRoute = str_replace('_delete', '_list', $currentRoute);
                        $url = $this->router->generate($listRoute);
                        $response = new RedirectResponse($url);
                        $response->send();
                        $args->stopPropagation();
                    }
                }
            }
        }
    }

    private function logChangeSet(string $actionType, $objectManager, $changeSet, $entity, $adminLogId):void
    {
        $conn = $objectManager->getConnection();
        foreach ($changeSet as $fieldName => [$oldValue, $newValue]) {
            $sql = '
                                INSERT INTO sonata_extra__admin_activity_change_log 
                                (admin_activity_log_id, entity_class, entity_id, field_name, old_value, new_value) 
                                VALUES 
                                (:adminLogId, :entityClass, :entityId, :fieldName, :oldValue, :newValue)
                            ';

            if ($oldValue instanceof \DateTime) {
                $oldValue = $oldValue->format('Y-m-d H:i:s');
            }

            if ($newValue instanceof \DateTime) {
                $newValue = $newValue->format('Y-m-d H:i:s');
            } elseif (is_object($newValue)) {
                $entity_serialized = $this->serializer->serialize($newValue, 'json');
                $entity_decoded = json_decode($entity_serialized);
                if (!empty($entity_decoded->id)) {
                    $newValue = $entity_decoded->id;
                } else {
                    $newValue = null;
                }
            }

            if ('update' == $actionType || 'delete' == $actionType) {
                $entityId = $entity->getId();
            } else {
                $entityId = null;
            }

            $params = [
                'adminLogId' => $adminLogId,
                'entityClass' => get_class($entity),
                'entityId' => $entityId,
                'fieldName' => $fieldName,
                'oldValue' => $oldValue,
                'newValue' => $newValue,
            ];

            $conn->executeQuery($sql, $params);
        }
    }

    private function logActivity(string $actionType, object $entity, ObjectManager $objectManager)
    {

        if ($this->excluded_entity($entity)) {
            return true;
        }
        $conn = $objectManager->getConnection();

        $className = get_class($entity);

        $user = null;
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser()) {
            $user = $token->getUser();
        }

        $log_message = $this->translator->trans('sonata-extra.approve.log_message', [],'PartitechSonataExtraBundle');
        $description = str_replace('[user]', $user ? $user->getUsername() : 'Anonymous.', $log_message);
        $description = str_replace('[action]', $actionType, $description);
        $description = str_replace('[entity]', $className, $description);

        $approval_message = $this->translator->trans('sonata-extra.approve.approval_message', [],'PartitechSonataExtraBundle');
        $approvalStatus = $this->needAproval() ? 0 : 1;
        if ($this->needAproval()) {
            $description .= '.<br> '.$approval_message;
        }

        $adminActivityLogRepo = $objectManager->getRepository(AdminActivityLog::class);
        $hasToken = $adminActivityLogRepo->findOneBy(['token' => $this->getToken()]);

        if ($hasToken) {
            $approvalStatus = 2; // if is a children of the main action then, status is 2
        }

        $sql = '
        INSERT INTO sonata_extra__admin_activity_log (date, action_type, resource, data, user_id, description, approval, token)
        VALUES (:date, :actionType, :resource, :data, :userId, :description, :approval, :token)
    ';

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'date' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'actionType' => $actionType,
            'resource' => get_class($entity),
            'data' => $this->serializer->serialize($entity, 'json'),
            'userId' => $user?->getId(),
            'description' => $description,
            'approval' => $approvalStatus,
            'token' => $this->getToken(),
        ]);

        $session = $this->requestStack->getCurrentRequest()->getSession();
        $session->getFlashBag()->clear();
        if ($this->needAproval()) {
            if ('create' == $actionType) {
                $success_message = $this->translator->trans('sonata-extra.approve.success_message.create', [],'PartitechSonataExtraBundle');
                $session->getFlashBag()->add('success', $success_message);
            } elseif ('update' == $actionType) {
                $success_message = $this->translator->trans('sonata-extra.approve.success_message.update', [],'PartitechSonataExtraBundle');
                $session->getFlashBag()->add('success', $success_message);
            } elseif ('delete' == $actionType) {
                $success_message = $this->translator->trans('sonata-extra.approve.success_message.delete', [],'PartitechSonataExtraBundle');
                $session->getFlashBag()->add('success', $success_message);
            }
        }

        return $conn->lastInsertId();
    }

    private function isAdmin():bool
    {
        $user = null;
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser()) {
            $user = $token->getUser();
        }
        return !empty($user);
    }

    private function excluded_entity($entity):bool
    {
        $excludedEntities = $this->parameterBag->get('sonata_approve_excluded_entities');

        return in_array(get_class($entity), $excludedEntities, true);
    }

    private function needAproval():bool
    {
        if(!$this->isAdmin()){
            return false;
        }
        if ($this->security->isGranted('ROLE_APPROVE')) {
            return false;
        } else {
            return true;
        }
    }

    public function getToken()
    {
        return $this->requestStack->getCurrentRequest()->attributes->get('_stopwatch_token');
    }
}
