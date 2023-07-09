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

    /**
     * @param EntityManagerInterface $entityManager
     * @param int $offerId
     * id оффера для изменения его количества в корзине
     * @param string $typeCange
     * тип изменения 'add' - добавить
     * 'delete' удалить
     */
    private function changeCart(
        EntityManagerInterface $entityManager, 
        int $offerId, 
        string $typeCange = 'add'
    ) {
        // проверка пользователя 
        $user = $this->getUser();
        if(is_null($user))
        {
            throw new Exception("User not auth");
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
        if(isset($productsCart[$offerId]))
        {
            $offer = $productsCart[$offerId];

            switch($typeCange) {
                case 'add':
                    $productsCart[$offerId]['count'] += 1;
                    break;

                case 'delete':
                    if($productsCart[$offerId]['count'] == 1)
                    {
                        unset($productsCart[$offerId]);
                    } else {
                        $productsCart[$offerId]['count'] -= 1;
                    }
                    break;

                case 'deleteHard':
                    unset($productsCart[$offerId]);
                    break;

                default:
                    throw new Exception("Unknown type");
            }
        } else if($typeCange == 'add') {
            // получаем продукт по офферу
            $client = $this->createRetailCrmClient();

            $requestProduct = new ProductsRequest();
            $requestProduct->filter = new ProductFilterType();
            $requestProduct->filter->offerIds = [$offerId];

            try {            
                $product = $client->store->products($requestProduct);
            } catch (Exception $exception) {
                dd($exception);
                exit(-1);
            }

            // проверяем, что что-то пришло
            if(empty($product->products))
            {
                throw new Exception("Offer not exists");
            }

            $product = $product->products[0];

            // ищу нужный оффер
            foreach($product->offers as $offer)
            {
                if($offer->id == $offerId)
                    break;
            }
            $offer = get_object_vars($offer);

            $productsCart[$offerId] = $offer;
            $productsCart[$offerId]['count'] = 1;
        } else {
            throw new Exception("You cannot delete a product that is not in the cart");
        }

        // обновляем/сохраняем корзину
        try {
            $cart->setProduct($productsCart);

            switch($typeCange) {
                case 'add':
                    $cart->setCountOfProducts($cart->getCountOfProducts() + 1);
                    $cart->setPrice($cart->getPrice() + $offer['price']);
                    break;
                case 'delete':
                    $cart->setCountOfProducts($cart->getCountOfProducts() - 1);
                    $cart->setPrice($cart->getPrice() - $offer['price']);
                    break;
                case 'deleteHard':
                    $cart->setCountOfProducts($cart->getCountOfProducts() - $offer['count']);
                    $cart->setPrice($cart->getPrice() - $offer['price']);
                    break;
                default:
                    throw new Exception("Unknown type");
            }
    
            $entityManager->persist($cart);
            $entityManager->flush();
        } catch (Exception $e) {
            throw new Exception("Failed to update buckets");
        }

        return true;
    }
    
    #[Route('/product/{offer_id}', name: 'app_product_add', methods: ['POST'])]
    public function addProductToCart(Request $request, EntityManagerInterface $entityManager)
    {
        dd($request);
        try {
            $this->changeCart(
                $entityManager,
                $request->get('offer_id')
            );
        } catch (Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'ok'
        ], Response::HTTP_OK);
    }

    #[Route('/product/{offer_id}', name: 'app_product_delete', methods: ['DELETE'])]
    public function deleteProductToCart(Request $request, EntityManagerInterface $entityManager)
    {
        try {
            $this->changeCart(
                $entityManager,
                $request->get('offer_id'),
                'delete'
            );
        } catch (Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'ok'
        ], Response::HTTP_OK);
    }

    #[Route('/product/{offer_id}/all', name: 'app_product_delete_all', methods: ['DELETE'])]
    public function deleteHardProductToCart(Request $request, EntityManagerInterface $entityManager)
    {
        try {
            $this->changeCart(
                $entityManager,
                $request->get('offer_id'),
                'deleteHard'
            );
        } catch (Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'status' => 'success',
            'message' => 'ok'
        ], Response::HTTP_OK);
    }
}