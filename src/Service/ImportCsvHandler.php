<?php

namespace Partitech\SonataExtra\Service;

use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Partitech\SonataExtra\Entity\Redirection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Service\Attribute\Required;

class ImportCsvHandler
{
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;

    #[Required]
    public function autowireDependencies(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): void {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @throws UnavailableStream
     * @throws Exception
     */
    public function handle(UploadedFile $file): void
    {
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0);

        $user = null;
        $token = $this->tokenStorage->getToken();
        if ($token && $token->getUser()) {
            $user = $token->getUser();
        }

        foreach ($csv as $record) {
            $redirection = new Redirection();
            $redirection->setEnabled(true)  // enabled est défini sur true par défaut
            ->setSource($record['Source'])
                ->setSourceHost($record['Source Host'])
                ->setTarget($record['Target'])
                ->setStatusCode((int) $record['Status-Code'])
                ->setUser($user ? $user->getUsername() : 'Anonyme');

            $this->entityManager->persist($redirection);
        }

        $this->entityManager->flush();
    }
}
