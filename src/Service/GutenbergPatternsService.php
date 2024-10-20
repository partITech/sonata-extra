<?php

namespace Partitech\SonataExtra\Service;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\Attribute\Required;

class GutenbergPatternsService
{
    private $configDir;
    private $mediaManager;
    private $entityManager;
    private $providerPool;

    #[Required]
    public function required(
        KernelInterface $kernel,
        MediaManagerInterface $mediaManager,
        EntityManagerInterface $entityManager,
        Pool $providerPool
    ): void {
        $this->configDir = $kernel->getProjectDir().'/config';
        $this->mediaManager = $mediaManager;
        $this->entityManager = $entityManager;
        $this->providerPool = $providerPool;
    }

    public function parseDirectory($directory): array
    {
        $finder = new Finder();
        $finder->files()->in($this->configDir.'/patterns/'.$directory)->name('*')->sortByName();

        $filesArray = [];
        foreach ($finder as $file) {
            $filesArray[] = [
                'path' => $file->getRealPath(),
                'filename' => $file->getFilename(),
            ];
        }

        return $filesArray;
    }

    public function getPatterns($directories)
    {
        $patterns = [];
        foreach ($directories as $d) {
            $files = $this->parseDirectory($d);
            foreach ($files as $f) {
                $patterns[] = $this->parsePattern($f);
            }
        }

        return $patterns;
    }

    public function parsePattern($f)
    {
        $patternFileContent = file_get_contents($f['path']);

        // Extract PHP comment block
        preg_match('/<\?php(.*?)\?>/s', $patternFileContent, $phpBlockMatches);
        $phpBlockContent = $phpBlockMatches[1] ?? '';

        $lines = preg_split('/\R/', $phpBlockContent);

        $metadata = [
            'title' => '',
            'description' => '',
            'categories' => '',
        ];

        foreach ($lines as $line) {
            $line = trim($line);
            if ('*' == substr($line, 0, 1)) {
                $line = trim(substr($line, 1));
                if (preg_match('/Title:\s*(.+)/', $line, $matches)) {
                    $metadata['title'] = trim($matches[1]);
                } elseif (preg_match('/Description:\s*(.+)/', $line, $matches)) {
                    $metadata['description'] = trim($matches[1]);
                } elseif (preg_match('/Categories:\s*(.+)/', $line, $matches)) {
                    $metadata['categories'] = array_map('trim', explode(',', $matches[1]));
                }
            }
        }

        // Extract content
        $content = preg_replace('/<\?php(.*?)\?>/s', '', $patternFileContent);

        // Build the final pattern data array
        $patternData = [
            'title' => $metadata['title'] ?? '',
            'name' => $metadata['title'] ?? '',
            'description' => $metadata['description'] ?? '',
            'categories' => $metadata['categories'] ?? '',
            'content' => $content,
        ];

        return $patternData;
    }

    public function getMediaPatterns($context, $categoryName = false)
    {
        if (!empty($categoryName)) {
            $categoryRepository = $this->entityManager->getRepository('SonataClassificationBundle:Category');
            $category = $categoryRepository->findOneBy(['name' => $categoryName]);

            /* $queryBuilder = $this->mediaManager->getEntityManager()->createQueryBuilder();
             $queryBuilder->select('m')
                 ->from('App\Entity\SonataMediaMedia', 'm')
                 ->orderBy('m.createdAt', 'DESC');*/

            $mediaItems = $this->mediaManager->findBy([
                'context' => $context,
                'category' => $category,
            ]);
        } else {
            $mediaItems = $this->mediaManager->findBy([
                'context' => $context,
            ]);
        }

        $patterns = [];
        foreach ($mediaItems as $mediaItem) {
            $patterns[] = $this->createPatternFromMedia($mediaItem);
        }

        return $patterns;
    }

    private function createPatternFromMedia($media): array
    {
        $provider = $this->providerPool->getProvider($media->getProviderName());
        $mediaUrl = $provider->generatePublicUrl($media, $provider->getFormatName($media, 'reference'));

        $patternContent = sprintf(
            '<!-- wp:image {"url":"%s","sizeSlug":"full","linkDestination":"none"} -->
                <figure class="wp-block-image size-large"><img src="%s" alt="%s"  /></figure>
                <!-- /wp:image -->',
            $mediaUrl,
            $mediaUrl,
            urlencode($media->getDescription() ?: $media->getName()),
        );

        return [
            'title' => $media->getName(),
            'name' => $media->getName(),
            'description' => $media->getDescription(),
            'content' => $patternContent,
            ];
    }
}
