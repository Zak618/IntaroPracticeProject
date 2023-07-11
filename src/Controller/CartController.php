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
    public function makeOrder(Request $request)
    {
        # пользователь для данных по умолчанию
        $user = $this->getUser();
        $user->crmLoad();

        $client = $this->createRetailCrmClient();
        #справочники доставки и оплаты
        try {
            $responseDelivery = $client->references->deliveryTypes();
            $responsePayment = $client->references->paymentTypes();
        } catch (Exception $e) {
            dd($e);
        }

        ## здесь добавить форму

        
        $form = $this->createForm(OrderType::class, $user);
        $form->handleRequest($request);
        
        ## проверка что форма прошла
        if ($form->isSubmitted() && $form->isValid()) 
        {
        $requestOrder = new OrdersCreateRequest();
        $requestOrder->order = new Order();

        $requestOrder->order->customer = new Customer();
        $requestOrder->order->customer->externalId = $user->getUuid();

        // заполнение данных о получателе
        # переделать на заполнение данных из request формы
        $requestOrder->order->customer->email  = $form->get('email')->getData();
        $requestOrder->order->customer->firstName = $form->get('firstname')->getData();
        $requestOrder->order->customer->lastName = $form->get('lastname')->getData();
        $requestOrder->order->customer->phones = [new CustomerPhone()];
        $requestOrder->order->customer->phones[0]->number = $form->get('phone')->getData();
       // $requestOrder->order->customer->address = new CustomerAddress();
        //$requestOrder->order->customer->address->text = $form-> get('address')->getData();
        

        // данные о платеже и доставке из 
        # заполнить из формы
        $requestOrder->order->payments = [new Payment()];
       // $requestOrder->order->payments[0]->type = 'bank-card';
        $requestOrder->order->payments[0]->type = $request->get('payment') == 1 ? 'bank-card' : 'cash';

        $requestOrder->order->delivery = new SerializedOrderDelivery();
        $requestOrder->order->delivery->service = new SerializedDeliveryService();
       // $requestOrder->order->delivery->service->code = 'self-delivery';
       $requestOrder->order->delivery->service->code  = $request->get('delivery') == 1 ? 'self-delivery' : 'delivery';

        $requestOrder->order->delivery->address = new OrderDeliveryAddress();
        //$requestOrder->order->delivery->address->text = "Адрес из формы";
        $requestOrder->order->delivery->address->text =$form-> get('address')->getData();

        $requestOrder->order->items = [];
        # перебрать корзиину и забрать офферы
        
        $cart = $user->getBasket();
        $basket_product=$cart->getProduct();
        //$basket_product->
        $basket_product = array_values($basket_product);
       // dd($basket_product[0]);

        //$item = new SerializedOrderProduct();
        //$item->offer = new SerializedOrderProductOffer();
        $item = new OrderProduct();






        //
        //выводит null. Я пока не знаю почему.
        //
        //
        // 
        dd(var_dump($basket_product[0]['id']));

        for ($i = 0; $i < count($basket_product); $i++) {
           
        $item->offer[$i]->id = $basket_product[$i]["id"];
        
        # order[items][][quantity]
        $requestOrder->order->items[$i]->quantity[$i]=$basket_product[$i]["quantity"];

        }



        try {
             $client->orders->create($requestOrder);
           // $client->customers->edit($user->getUuid(), $requestCustomer);
        } catch (Exception $e) {
            dd($e);
        }

        //$requestOrder->save($user, true);

        return $this->redirectToRoute('app_store', [], Response::HTTP_SEE_OTHER);
    }

    return $this->renderForm('cart/create_order.html.twig', [
        //'client' => $user,
        'form' => $form,
        'header' => $this->getHeader()
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