<?php

namespace Partitech\SonataExtra\Service\ImportWordpress;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Partitech\SonataExtra\Service\ImportWordpress\Api\Users;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class UserSync
{
    private ?array $users = null;
    private string $adminUserClass;
    private EntityRepository $adminUserRepository;

    /**
     * @throws Exception
     */
    public function __construct(
        private readonly Users $usersApi,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        ParameterBagInterface $parameterBag,
        private readonly Event $event,
    ) {
        $this->adminUserClass = $parameterBag->get('sonata.user.user.class');
        $this->adminUserRepository = $this->entityManager->getRepository($this->adminUserClass);
    }
    
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function sync()
    : void
    {
        $this->getUsers();
        $this->event->setCurrentStep(Event::USERS_STEP);
        $this->event->setCount(count($this->users));
        
        foreach ($this->users as $key => $wordpressUser) {
            $this->event->setJob($wordpressUser['name']);
            
            $userName = str_replace(' ', '.', $wordpressUser['name']);
            $sonataUser = $this->adminUserRepository->findOneBy(['username' => $userName]);

            if (!$sonataUser) {
                $sonataUser = $this->creatEntity($userName);
            }
            $this->users[$key]['entity'] = $sonataUser;
        }

        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $this->logger->error('Failed to save users to database: '.$e->getMessage());
        }
    }

    private function creatEntity(string $userName): object
    {
        $sonataUser = new $this->adminUserClass();
        
        $sonataUser->setUsername($userName);
        $sonataUser->setEmail(substr(str_shuffle(md5(microtime())), 0, 10).'@john-doe.com');
        $sonataUser->setPassword(substr(str_shuffle(md5(microtime())), 0, 150));
        $sonataUser->setEnabled(false);
        
        try {
            $this->entityManager->persist($sonataUser);
            $this->entityManager->flush();
            
        } catch (Exception $e) {
            $this->logger->error(sprintf('Failed to process user "%s": %s', $userName, $e->getMessage()));
        }
        
        return $sonataUser;
    }
    
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getUsers(): ?array
    {
        if (is_null($this->users)) {
            $this->users = $this->usersApi->getAll();
        }

        return $this->users;
    }

    public function getUserByWpId(int $id): ?object
    {
        foreach ($this->users as $user) {
            if ($user['id'] !== $id) {
                continue;
            }

            return $user['entity'] ?? null;
        }

        return null;
    }
}
