<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Repository\BasketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RetailCrm\Api\Model\Filter\Store\ProductFilterType;
use RetailCrm\Api\Model\Request\Store\ProductsRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreController extends BaseController
{
    #[Route('', name: 'app_store')]
    public function index(): Response
    {
        $user = $this->getUser();
        // $user->crmLoad();
        // dd($user);
        return $this->render('store/index.html.twig', [
            'header' => $this->getHeader()
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_page')]
    public function page(Request $request): Response
    {
        $currentPage = $request->query->getInt('page', 1);

        $client = $this->createRetailCrmClient();

        $requestProducts = new ProductsRequest();
        $requestProducts->filter = new ProductFilterType();
        $requestProducts->filter->groups = [$request->get('id')];
        $requestProducts->page = $currentPage;

        try {            
            $response = ($client->store->products($requestProducts));
        } catch (Exception $exception) {
            dd($exception);
            exit(-1);
        }
        
        return $this->render('store/category.html.twig', [
            'header' => $this->getHeader(),
            'products' => $response->products,
            'totalPageCount' => $response->pagination->totalPageCount
        ]);
    }

    // #[Route('/product/{offer_id}', name: 'app_product_add', methods: ['POST'])]
    #[Route('/product/{offer_id}/add', name: 'app_product_add')]
    public function addProductToCart(Request $request, EntityManagerInterface $entityManager)
    {
        // проверка пользователя 
        $user = $this->getUser();
        if(is_null($user))
        {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'User not auth'
            ], Response::HTTP_BAD_REQUEST);
        }

        // получаем оффер
        $client = $this->createRetailCrmClient();

        $requestProduct = new ProductsRequest();
        $requestProduct->filter = new ProductFilterType();
        $requestProduct->filter->offerIds = [$request->get('offer_id')];

        try {            
            $product = $client->store->products($requestProduct);
        } catch (Exception $exception) {
            dd($exception);
            exit(-1);
        }

        // проверяем, что что-то пришло
        if(empty($product->products))
        {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Product not exists'
            ], Response::HTTP_BAD_REQUEST);
        }

        $product = $product->products[0];

        // получаем запись корзины из бд
        if(($cart = $user->getBasket()) == null){
            // создаем запись корзины в бд
            $cart = new Basket();
            $cart->setIdClient($user);
            $cart->setDiscount(1);
            $cart->setCountOfProducts(0);
            $cart->setPrice(0);
        }

        $productsCart = $cart->getProduct();

        
        // ищу нужный оффер
        foreach($product->offers as $offer)
        {
            if($offer->id == $request->get('offer_id'))
                break;
        }

        // проверяем, есть ли запись
        if(isset($productsCart[$request->get('offer_id')]))
        {
            $productsCart[$request->get('offer_id')]['count'] += 1;
        } else {
            $productsCart[$request->get('offer_id')] = get_object_vars($offer);
            $productsCart[$request->get('offer_id')]['count'] = 1;
        }

        // обновляем/сохраняем корзину
        $cart->setProduct($productsCart);
        $cart->setCountOfProducts($cart->getCountOfProducts() + 1);
        $cart->setPrice($cart->getPrice() + $offer->price);

        $entityManager->persist($cart);
        $entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'ok'
        ], Response::HTTP_OK);
    }

    // детальная страница товара
    #[Route('/product/{id}', name: 'app_product', methods: ['GET'])]
    public function detailPage(Request $request): Response
    {
        $client = $this->createRetailCrmClient();

        $requestProduct = new ProductsRequest();
        $requestProduct->filter = new ProductFilterType();
        $requestProduct->filter->ids = [$request->get('id')];

        try {            
            $response = ($client->store->products($requestProduct))->products[0];
            // dd($response);
        } catch (Exception $exception) {
            dd($exception);
            exit(-1);
        }

        return $this->render('store/product.html.twig', [
            'header' => $this->getHeader(),
            'product' => $response
        ]);
    }
}
