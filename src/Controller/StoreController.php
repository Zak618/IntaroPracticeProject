<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreController extends BaseController
{
    #[Route('', name: 'app_store')]
    public function index(): Response
    {
        dd($this->getHeader());
        return $this->render('store/index.html.twig', [
            'controller_name' => 'StoreController',
            'title' => 'djf', 
            'header' => $this->getHeader()
        ]);
    }
}
