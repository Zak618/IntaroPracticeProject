<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Repository\BasketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use App\Form\OrderType;
use RetailCrm\Api\Model\Callback\Entity\Delivery\Customer;
use RetailCrm\Api\Model\Entity\Delivery\SerializedOrderProduct;
use RetailCrm\Api\Model\Entity\Loyalty\SerializedOrderProductOffer;
use RetailCrm\Api\Model\Entity\Orders\Delivery\OrderDeliveryAddress;
use RetailCrm\Api\Model\Entity\Orders\Delivery\SerializedDeliveryService;
use RetailCrm\Api\Model\Entity\Orders\Delivery\SerializedOrderDelivery;
use RetailCrm\Api\Model\Entity\Orders\Order;
use RetailCrm\Api\Model\Entity\Orders\Payment;
use RetailCrm\Api\Model\Entity\References\DeliveryService;
use RetailCrm\Api\Model\Filter\Store\ProductFilterType;
use RetailCrm\Api\Model\Request\Orders\OrdersCreateRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersRequest;
use RetailCrm\Api\Model\Request\Store\ProductsRequest;
use RetailCrm\Api\ResourceGroup\Delivery;
use RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RetailCrm\Api\Model\Entity\Customers\CustomerPhone;
use RetailCrm\Api\Model\Entity\Customers\CustomerAddress;
use RetailCrm\Api\Model\Entity\Orders\Items\Offer;
use RetailCrm\Api\Model\Entity\Orders\SerializedRelationCustomer;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends BaseController
{ 
    #[Route('/product/{offer_id}', name: 'app_product_add', methods: ['POST'])]
    #[Route('/product/{offer_id}', name: 'app_product_delete', methods: ['DELETE'])]
    #[Route('/product/{offer_id}/all', name: 'app_product_delete_all', methods: ['DELETE'])]
    public function addProductToCart(Request $request, EntityManagerInterface $entityManager)
    {
        try {
            $this->changeCart(
                $entityManager,
                $request->get('offer_id'),
                $request->get('_route')
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

    #[Route('/cart', name: 'app_cart')]
    public function index()
    {
        $user = $this->getUser();
        if($user)
        {
            $cart = $user->getBasket();
        }

        $header = $this->getHeader();
        
        return $this->render('cart/index.html.twig', [
            'header' => $header, 
            'cart' => isset($cart) ? $cart : null
        ]);
    }

    #[Route('/order/create', name: 'app_make_order')]
    public function makeOrder(Request $request, EntityManagerInterface $entityManager)
    {
        # пользователь для данных по умолчанию
        $header = $this->getHeader();

        $user = $this->getUser();
        if(is_null($user))
        {
            return $this->renderForm('cart/create_order.html.twig', [
                'error' => 'Пользователь не авторизован',
                'header' => $header
            ]);
        }

        $cart = $user->getBasket();

        if(is_null($cart) || empty($cart->getProduct()))
        {
            return $this->renderForm('cart/create_order.html.twig', [
                'error' => 'Корзина пуста, нельзя сделать заказ',
                'header' => $header
            ]);
        }

        $client = $this->createRetailCrmClient();

        #справочники доставки и оплаты
        try {
            $responseDelivery = $client->references->deliveryTypes();
            $responsePayment = $client->references->paymentTypes();
        } catch (Exception $e) {
            dd($e);
        }

        $deliveryTypes = [];
        foreach($responseDelivery->deliveryTypes as $item)
        {
            $deliveryTypes[$item->name] = $item->code;
        }

        $paymentTypes = [];
        foreach($responsePayment->paymentTypes as $item)
        {
            $paymentTypes[$item->name] = $item->code;
        }

        
        $form = $this->createForm(OrderType::class, $user, [
            'deliveryChoices' => $deliveryTypes,
            'paymentChoices' => $paymentTypes,
        ]);
        $form->handleRequest($request);
        
        ## проверка что форма прошла
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $requestOrder = new OrdersCreateRequest();
            $requestOrder->order = new Order();

            $requestOrder->order->customer = new SerializedRelationCustomer();
            $requestOrder->order->customer->externalId = $user->getUuid();

            // заполнение данных о получателе
            $requestOrder->order->email  = $form->get('email')->getData();
            $requestOrder->order->firstName = $form->get('firstname')->getData();
            $requestOrder->order->lastName = $form->get('lastname')->getData();
            $requestOrder->order->patronymic = $form->get('patronymic')->getData();
            $requestOrder->order->phone = $form->get('phone')->getData();

            // данные о платеже и доставке
            $requestOrder->order->payments = [new Payment()];
            $requestOrder->order->payments[0]->type = $form->get('payment')->getData();

            $requestOrder->order->delivery = new SerializedOrderDelivery();
            $requestOrder->order->delivery->code = $form->get('delivery')->getData();
            $requestOrder->order->delivery->service = new SerializedDeliveryService();
            $requestOrder->order->delivery->service->code = $form->get('delivery')->getData();
            // адрес доставки
            $requestOrder->order->delivery->address = new OrderDeliveryAddress();
            $requestOrder->order->delivery->address->text = $form-> get('address')->getData();


            $requestOrder->order->items = [];
            # перебрать корзиину и забрать офферы
            foreach(($cart->getProduct()) as $product)
            {
                $item = new OrderProduct();
                $item->offer = new Offer();
                $item->offer->id = $product['id'];
                $item->quantity = $product['count'];

                $requestOrder->order->items[] = $item;
            }

            try {
                $client->orders->create($requestOrder);
                // очищаем корзину
                $cart->setProduct([]);
                $cart->setCountOfProducts(0);
                $cart->setPrice(0);

                $entityManager->persist($cart);
                $entityManager->flush();
            } catch (Exception $e) {
                dd($e);
            }

            return $this->redirectToRoute('app_orders', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('cart/create_order.html.twig', [
            'form' => $form,
            'header' => $this->getHeader(),
            'cart' => $cart->getProduct()
        ]);
    }


    /**
     * @param EntityManagerInterface $entityManager
     * @param int $offerId
     * id оффера для изменения его количества в корзине
     * @param string $typeChange
     * тип изменения 'app_product_add' - добавить
     * 'app_product_delete' удалить единицу
     * 'app_product_delete_all' удалить полностью оффер
     */
    private function changeCart(
        EntityManagerInterface $entityManager, 
        int $offerId, 
        string $typeChange = 'app_product_add'
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

            switch($typeChange) {
                case 'app_product_add':
                    $productsCart[$offerId]['count'] += 1;
                    break;

                case 'app_product_delete':
                    if($productsCart[$offerId]['count'] == 1)
                    {
                        unset($productsCart[$offerId]);
                    } else {
                        $productsCart[$offerId]['count'] -= 1;
                    }
                    break;

                case 'app_product_delete_all':
                    unset($productsCart[$offerId]);
                    break;

                default:
                    throw new Exception("Unknown type");
            }
        } else if($typeChange == 'app_product_add') {
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
            throw new Exception("You cannot app_product_delete a product that is not in the cart");
        }

        // обновляем/сохраняем корзину
        try {
            $cart->setProduct($productsCart);

            switch($typeChange) {
                case 'app_product_add':
                    $cart->setCountOfProducts($cart->getCountOfProducts() + 1);
                    $cart->setPrice($cart->getPrice() + $offer['price']);
                    break;
                case 'app_product_delete':
                    $cart->setCountOfProducts($cart->getCountOfProducts() - 1);
                    $cart->setPrice($cart->getPrice() - $offer['price']);
                    break;
                case 'app_product_delete_all':
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
}