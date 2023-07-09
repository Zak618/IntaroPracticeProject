<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Repository\BasketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RetailCrm\Api\Model\Filter\Store\ProductFilterType;
use RetailCrm\Api\Model\Request\Store\ProductsRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends BaseController
{

    #[Route('/product/{offer_id}', name: 'app_product_add', methods: ['POST'])]
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

        // проверяем, есть ли оффер в корзине
        if(isset($productsCart[$request->get('offer_id')]))
        {
            $productsCart[$request->get('offer_id')]['count'] += 1;
        } else {
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

    #[Route('/product/{offer_id}', name: 'app_product_delete', methods: ['DELETE'])]
    public function deleteProductToCart(Request $request, EntityManagerInterface $entityManager)
    {
    }
}