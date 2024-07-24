<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;

use Symfony\Component\Filesystem\Path;

class FrontendController extends AbstractController
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    //#[Route('/{uri}', requirements: ['uri' => '.*'], utf8: true)]
    public function index(string $uri): Response
    {
        $uri = $uri ? $uri : 'index.html';
        $rootDir = $this->getParameter('kernel.project_dir');
        $filename = Path::join($rootDir, 'frontend', $uri);

        if ($this->filesystem->exists($filename)) {
            $response = new BinaryFileResponse($filename);
        } else {
            throw $this->createNotFoundException();
        }
        return $response;
    }
}
