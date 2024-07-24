<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/test', methods: ['GET', 'HEAD'], name: 'test')]
    public function test(): Response
    {
        return $this->render("admin/index.html.twig", [
            'controller_name' => 'test'
        ]);
    }

    #[Route('/{path}', methods: ['GET', 'HEAD'], name: 'any')]
    public function path($path): Response
    {
        return $this->render("admin/index.html.twig", [
            'controller_name' => $path
        ]);
    }
}
