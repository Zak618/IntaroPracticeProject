<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
class HeaderController extends AbstractController
{
    #[Route('/header', name: 'header')]
    /**
     * @Route("/header", name="header")
     */
    public function index(): Response
    {
        $logo = 'Путь_к_логотипу';
        $shopName = 'Название_магазина';
        
        // Получение информации о пользователе (авторизован ли он)
        $user = $this->getUser();
        $isAuthenticated = $user !== null;
        
        // Определение ссылок для авторизованных и неавторизованных пользователей
        $loginLink = $this->generateUrl('login');
        $registerLink = $this->generateUrl('register');
        $accountLink = $isAuthenticated ? $this->generateUrl('account') : null;
        $cartLink = $isAuthenticated ? $this->generateUrl('cart') : null;
        
        // Получение разделов каталога 1 уровня (можно использовать данные из базы данных)
        $categories = [
            'Раздел 1',
            'Раздел 2',
            'Раздел 3',
        ];
        
        return $this->render('header/index.html.twig', [
            'logo' => $logo,
            'shopName' => $shopName,
            'loginLink' => $loginLink,
            'registerLink' => $registerLink,
            'accountLink' => $accountLink,
            'cartLink' => $cartLink,
            'categories' => $categories,
        ]);
    }
}
