<?php

namespace App\Controller;


use App\Form\ClientType;
use App\Repository\ClientRepository;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use RetailCrm\Api\Model\Entity\Customers\Customer;
use RetailCrm\Api\Model\Entity\Customers\CustomerPhone;
use RetailCrm\Api\Model\Entity\Customers\CustomerAddress;
use RetailCrm\Api\Model\Filter\Orders\OrderFilter;
use RetailCrm\Api\Model\Request\Customers\CustomersEditRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersStatusesRequest;

#[Route('/client')]
class ClientController extends BaseController
{
    #[Route('', name: 'app_client_index', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('client/show.html.twig', [
            'header' => $this->getHeader()
        ]);
    }


    #[Route('/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit_client(
        Request $request,
        ClientRepository $clientRepository
    ): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(ClientType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $user_api =$this->createRetailCrmClient();

            // заготовка для отправления запроса
            $requestCustomer = new CustomersEditRequest();
            $requestCustomer->customer= new Customer();
            
            $requestCustomer->customer->email  = $form->get('email')->getData();
            $requestCustomer->customer->firstName = $form->get('firstname')->getData();
            $requestCustomer->customer->lastName = $form->get('lastname')->getData();
            $requestCustomer->customer->patronymic = $form->get('patronymic')->getData();
            $requestCustomer->customer->phones = [new CustomerPhone()];
            $requestCustomer->customer->phones[0]->number = $form->get('phone')->getData();
            $requestCustomer->customer->birthday = $form->get('birthday')->getData();
            $requestCustomer->customer->sex = $request->get('sex') == 2 ? 'female' : 'male';
            $requestCustomer->customer->address = new CustomerAddress();
            $requestCustomer->customer->address->text = $form-> get('address')->getData();
            
            
            try {
                $user_api->customers->edit($user->getUuid(), $requestCustomer);
            } catch (Exception $e) {
                dd($e);
            }

            $clientRepository->save($user, true);

            return $this->redirectToRoute('app_store', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client/edit.html.twig', [
            'client' => $user,
            'form' => $form,
            'header' => $this->getHeader()
        ]);
    }

    #[Route('/orders', name: 'app_orders')]
    public function ordersIndex(Request $request)
    {
        $user = $this->getUser();
        $header = $this->getHeader();

        if(is_null($user)) {
            return $this->render('client/order.html.twig', [
                'header' => $header,
                'error' => 'User not auth'
            ]);
        }

        $client = $this->createRetailCrmClient();
        $currentPage = $request->query->getInt('page', 1);

        // запрос заказов
        $orderRequest = new OrdersRequest();
        $orderRequest->filter = new OrderFilter();
        $orderRequest->filter->customerExternalId = $user->getUuid();
        $orderRequest->page = $currentPage;

        try {
            $response = $client->orders->list($orderRequest);
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        // запрос справочника статусов заказа
        try {
            $responseStatuses = $client->references->statuses();
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return $this->render('client/order.html.twig', [
            'header' => $header,
            'orderds' => $response->orders,
            'totalPageCount' => $response->pagination->totalPageCount,
            'currentPage' => $currentPage,
            'statuses' => $responseStatuses->statuses
        ]);
    }
}
