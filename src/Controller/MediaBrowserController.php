<?php

namespace Partitech\SonataExtra\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Partitech\SonataExtra\Service\Media as MediaService;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaBrowserController extends AbstractController
{
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly MediaManagerInterface $mediaManager,
        private readonly ParameterBagInterface $parameterBag,
        private readonly PaginatorInterface $paginator
    ) {
    }

    #[Route('/admin/SonataExtra/ckeditor/media-browser', name: 'sonata_extra_ckeditor_media_browser', methods: ['POST', 'GET'])]
    public function mediaBrowser(Request $request): Response
    {
        $media_class = $this->parameterBag->get('partitech_sonata_extra.class.media');

        $search = $request->query->get('search');
        $queryBuilder = $this->mediaManager->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('m')
            ->from($media_class, 'm')
            ->orderBy('m.createdAt', 'DESC');

        if ($search) {
            $queryBuilder->where('m.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            20 // Limit per page
        );

        $mediaUrls = [];
        foreach ($pagination as $media) {
            $mediaUrls[$media->getId()] = $this->mediaService->getPublicLink($media);
        }

        return $this->render('@PartitechSonataExtra/media_browser/ckeditor.html.twig', [
            'CKEditorFuncNum' => $request->query->get('CKEditorFuncNum'),
            'pagination' => $pagination,
            'mediaUrls' => $mediaUrls,
            'search' => $search,
        ]);
    }

    #[Route('/admin/SonataExtra/ckeditor/config', name: 'sonata_extra_ckeditor_config', methods: ['POST', 'GET'])]
    public function ckeditorConfig(UrlGeneratorInterface $router): Response
    {
        $config = [
            'filebrowserBrowseUrl' => $router->generate(
                'sonata_extra_ckeditor_media_browser',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'filebrowserImageBrowseUrl' => $router->generate(
                'sonata_extra_ckeditor_media_browser',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];

        return $this->json($config);
    }

    #[Route('/admin/sonata-extra/gutenberg/upload/{context}', name: 'sonata_extra_gutenberg_media_upload', methods: ['POST', 'GET'])]
    public function upload(
        string $context,
        Request $request,
    ): Response {
        /** @var UploadedFile[] $uploadedFiles */
        $uploadedFiles = $request->files->get('file');
        $mediaEntities = [];

        foreach ($uploadedFiles as $uploadedFile) {
            $media = $this->mediaService->createMediaByContent(
                name       : $uploadedFile->getClientOriginalName(),
                content    : $uploadedFile->getContent(),
                context    : null,
                contextName: $context,
            );

            $mediaEntities[] = [
                'url' => $this->mediaService->getPublicLink($media),
                'title' => $media->getName(),
                'caption' => '',  // @todo: Ajoutez la légende si disponible
            ];
        }

        return $this->json($mediaEntities);
    }

    #[Route('/admin/sonata-extra/eme/upload/{context}', name: 'sonata_extra_eme_media_upload', methods: ['POST'])]
    public function uploadMarkdown(
        string $context,
        Request $request,
    ): Response {

        if($request->files->count() === 0){
            return $this->json(null);
        }


        /** @var UploadedFile[] $uploadedFiles */
        $uploadedFile = $request->files->getIterator()->current();

        $media = $this->mediaService->createMediaByContent(
            name       : $uploadedFile->getClientOriginalName(),
            content    : $uploadedFile->getContent(),
            context    : null,
            contextName: $context,
        );

        $jsonResponse = [
            'data' => [
                'filePath' => $this->mediaService->getPublicLink($media)
            ]
        ];

        return $this->json($jsonResponse);
    }

}
