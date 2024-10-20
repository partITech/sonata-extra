<?php

namespace Partitech\SonataExtra\Service;

use App\Entity\SonataClassificationContext;
use App\Entity\SonataMediaMedia;
use App\Repository\SonataClassificationContextRepository;
use Psr\Log\LoggerInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

class Media
{
    public const MEDIA_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'gif',
    ];

    public const MEDIA_MIME_TYPES = [
        'image/jpeg', // pour jpg et jpeg
        'image/png',
        'image/gif',
    ];

    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly SonataClassificationContextRepository $contextRepository,
        private readonly MediaManagerInterface $mediaManager,
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $logger,
        private readonly Pool $providerPool,
        private Environment $twig,
    ) {
    }
    
    public function createOrGetMediaByContent(
        string $name,
        string $content,
        SonataClassificationContext $context = null,
        string $contextName = null
    ): SonataMediaMedia {
        /** @var SonataMediaMedia $media */
        $media = $this->mediaManager->findOneBy(['name' =>$name ]);
        if(!is_null($media)){
            $provider = $this->providerPool->getProvider($media->getProviderName());
            $format = $provider->getFormatName($media, 'reference');
            if($provider->getReferenceFile($media, $format)->exists()){
                return $media;
            }
            
        }
        
        return $this->createMediaByContent($name, $content, $context, $contextName, $media);
    }

    public function createMediaByContent(
        string $name,
        string $content,
        SonataClassificationContext $context = null,
        string $contextName = null,
        $media = null
    ): SonataMediaMedia {

        
        if (is_null($context)) {
            $context = $this->contextRepository->first();
        }
        $file = $this->createFile($content);

        $media_class = $this->parameterBag->get('partitech_sonata_extra.class.media');
        
        if(is_null($media)){
            $media = new $media_class();
        }
        $media->setBinaryContent($file);
        $media->setContext($context->getId());
        $media->setName($name);
        $media->setEnabled(true);
        try {
            // this fix https://github.com/sonata-project/SonataMediaBundle/issues/855#issuecomment-342862881
            // sometimes… Thanks to him for the resolution
            $media_sizes = getimagesize($file);
            $media->setWidth($media_sizes[0]);
            $media->setHeight($media_sizes[1]);
        } catch (\Exception $e) {
            // avoid error.
            // Unfortunately sometimes it fails.
            // But not the global process.
            // So job need to continue.
        }

        $media->setProviderName($this->getProvider($file));

        try {
            $this->mediaManager->save($media);
        } catch (\Exception $e) {
            $this->logger->error('Failed to save media: '.$name.', '.$e->getMessage());
        }

        return $media;
    }

    private function createFile($content): File
    {
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'media_');
        $this->filesystem->appendToFile($tmpFilePath, $content);

        return new File($tmpFilePath);
    }

    private function getProvider(File $file): string
    {
        if (!empty($file->getExtension())) {
            if (in_array($file->getExtension(), self::MEDIA_EXTENSIONS)) {
                return 'sonata.media.provider.image';
            }
        }

        if (!empty($file->getMimeType())) {
            if (in_array($file->getMimeType(), self::MEDIA_MIME_TYPES)) {
                return 'sonata.media.provider.image';
            }
        }

        return 'sonata.media.provider.file';
    }

    public function getPublicLink(\Sonata\MediaBundle\Model\MediaInterface $media): ?string
    {
        $provider = $this->providerPool->getProvider($media->getProviderName());
        $format = $provider->getFormatName($media, 'reference');

        return $provider->generatePublicUrl($media, $format);
    }
    
    /**
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function renderMediaHtml(\Sonata\MediaBundle\Model\MediaInterface $media): string
    {
        $template = "
                {{
                    sonata_media( media, 'reference')
                }}
        ";
        return $this->twig->createTemplate($template)->render(['media' => $media]);
    }
}
