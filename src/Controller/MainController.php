<?php

namespace Partitech\SonataExtra\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/sonata-extra/hello-world', name: 'partitech_hello_world')]
    public function __invoke(): Response
    {
        return $this->render('@PartitechSonataExtra/hello-world.html.twig');
    }
}
